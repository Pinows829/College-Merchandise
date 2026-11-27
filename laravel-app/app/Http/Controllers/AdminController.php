<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Course;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    private const COLLEGE_CODES = ['CCICT', 'CAS', 'COE', 'COT', 'COED', 'CME'];
    private const STATUSES = ['Pending', 'Paid', 'Preparing', 'Ready for Pickup', 'Completed'];

    private function requireAdmin(): void
    {
        abort_unless(auth()->user()->role === 'admin', 403);
    }

    private function colleges()
    {
        return College::whereIn('code', self::COLLEGE_CODES)
            ->orderByRaw("FIELD(code, 'CCICT','CAS','COE','COT','COED','CME')")
            ->get();
    }

    public function dashboard()
    {
        $this->requireAdmin();
        $summary = [
            'products' => Product::count(),
            'orders' => Order::count(),
            'pending' => Order::where('status', 'Pending')->count(),
            'revenue' => Order::whereIn('status', ['Paid', 'Preparing', 'Ready for Pickup', 'Completed'])->sum('total'),
            'stock' => ProductSize::sum('stock'),
        ];
        $recent = Order::with('user')->latest('id')->limit(8)->get();
        $lowStock = ProductSize::with('product')->where('stock', '<=', 5)->orderBy('stock')->limit(6)->get();

        return view('admin.dashboard', compact('summary', 'recent', 'lowStock'));
    }

    public function products(Request $request)
    {
        $this->requireAdmin();
        $query = Product::with(['college', 'course'])->withSum('sizes', 'stock')->latest('id');
        if ($request->filled('q')) {
            $search = trim($request->string('q')->toString());
            $query->where(function ($products) use ($search) {
                $products->where('name', 'like', "%{$search}%")
                    ->orWhereHas('course', fn ($course) => $course->where('name', 'like', "%{$search}%"));
            });
        }
        $products = $query->paginate(12)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function createProduct()
    {
        $this->requireAdmin();
        $product = new Product(['category' => 'Shirts', 'price' => '']);
        $sizesText = "S:0\nM:0\nL:0\nXL:0";
        $colleges = $this->colleges();
        $courses = Course::whereIn('college_id', $colleges->pluck('id'))->orderBy('name')->get();

        return view('admin.products.form', compact('product', 'sizesText', 'colleges', 'courses'));
    }

    public function storeProduct(Request $request)
    {
        $this->requireAdmin();
        return $this->saveProduct($request);
    }

    public function editProduct(Product $product)
    {
        $this->requireAdmin();
        $product->load('sizes');
        $sizesText = $product->sizes->map(fn ($size) => $size->size.':'.$size->stock)->implode("\n");
        if ($sizesText === '') {
            $sizesText = "S:0\nM:0\nL:0\nXL:0";
        }
        $colleges = $this->colleges();
        $courses = Course::whereIn('college_id', $colleges->pluck('id'))->orderBy('name')->get();

        return view('admin.products.form', compact('product', 'sizesText', 'colleges', 'courses'));
    }

    public function updateProduct(Request $request, Product $product)
    {
        $this->requireAdmin();
        return $this->saveProduct($request, $product);
    }

    private function saveProduct(Request $request, ?Product $product = null)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'college_id' => ['required', 'integer', 'exists:colleges,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'category' => ['required', 'in:Shirts,Jackets,Accessories'],
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'sizes' => ['required', 'string', 'max:3000'],
        ]);

        abort_unless(College::whereKey($validated['college_id'])->whereIn('code', self::COLLEGE_CODES)->exists(), 422);
        $courseId = $validated['course_id'] ?: null;
        if ($courseId && ! Course::whereKey($courseId)->where('college_id', $validated['college_id'])->exists()) {
            throw ValidationException::withMessages(['course_id' => 'Choose a course that belongs to the selected college.']);
        }

        $sizes = $this->parseSizes($validated['sizes']);
        DB::transaction(function () use ($product, $validated, $courseId, $sizes) {
            $data = [
                'college_id' => $validated['college_id'],
                'course_id' => $courseId,
                'name' => trim($validated['name']),
                'category' => $validated['category'],
                'description' => trim($validated['description']),
                'price' => $validated['price'],
            ];
            $product ??= new Product();
            $product->fill($data)->save();

            foreach ($sizes as $size => $stock) {
                ProductSize::updateOrCreate(
                    ['product_id' => $product->id, 'size' => $size],
                    ['stock' => $stock],
                );
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Product saved.');
    }

    private function parseSizes(string $input): array
    {
        $sizes = [];
        foreach (preg_split('/\R/', $input) as $line) {
            if (trim($line) === '') {
                continue;
            }
            if (! preg_match('/^\s*([^:]{1,30})\s*:\s*(\d+)\s*$/', $line, $matches)) {
                throw ValidationException::withMessages(['sizes' => 'Use one size and stock amount per line, for example M:10.']);
            }
            $name = trim($matches[1]);
            if (isset($sizes[$name])) {
                throw ValidationException::withMessages(['sizes' => 'Each size may be listed only once.']);
            }
            $sizes[$name] = (int) $matches[2];
        }
        if ($sizes === []) {
            throw ValidationException::withMessages(['sizes' => 'Add at least one size and stock amount.']);
        }

        return $sizes;
    }

    public function courses()
    {
        $this->requireAdmin();
        $colleges = $this->colleges();
        $courses = Course::with('college')->whereIn('college_id', $colleges->pluck('id'))
            ->orderBy('college_id')->orderBy('name')->get();

        return view('admin.courses', compact('colleges', 'courses'));
    }

    public function storeCourse(Request $request)
    {
        $this->requireAdmin();
        $validated = $request->validate([
            'college_id' => ['required', 'integer', 'exists:colleges,id'],
            'name' => ['required', 'string', 'max:180'],
        ]);
        abort_unless(College::whereKey($validated['college_id'])->whereIn('code', self::COLLEGE_CODES)->exists(), 422);
        Course::firstOrCreate(['college_id' => $validated['college_id'], 'name' => trim($validated['name'])]);

        return redirect()->route('admin.courses.index')->with('success', 'Course collection added.');
    }

    public function orders()
    {
        $this->requireAdmin();
        $orders = Order::with('user')->latest('id')->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function showOrder(Order $order)
    {
        $this->requireAdmin();
        $order->load(['user', 'items.productSize.product']);

        return view('admin.orders.show', ['order' => $order, 'statuses' => self::STATUSES]);
    }

    public function updateOrder(Request $request, Order $order)
    {
        $this->requireAdmin();
        $validated = $request->validate(['status' => ['required', 'in:'.implode(',', self::STATUSES)]]);
        $order->status = $validated['status'];
        $order->save();

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order status updated.');
    }
}
