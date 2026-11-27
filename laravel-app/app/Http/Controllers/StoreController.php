<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Course;
use App\Models\Product;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    private const COLLEGE_CODES = ['CCICT', 'CAS', 'COE', 'COT', 'COED', 'CME'];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $colleges = College::whereIn('code', self::COLLEGE_CODES)
            ->orderByRaw("FIELD(code, 'CCICT','CAS','COE','COT','COED','CME')")
            ->get();

        $query = Product::with(['college', 'course', 'sizes'])
            ->whereHas('college', fn ($college) => $college->whereIn('code', self::COLLEGE_CODES));

        if ($search !== '') {
            $query->where(function ($products) use ($search) {
                $products->where('name', 'like', "%{$search}%")
                    ->orWhereHas('course', fn ($course) => $course->where('name', 'like', "%{$search}%"));
            });
        }

        $products = $query->latest('id')->limit(12)->get();

        return view('store.home', compact('colleges', 'products', 'search'));
    }

    public function college(Request $request, College $college)
    {
        abort_unless(in_array($college->code, self::COLLEGE_CODES, true), 404);

        $courses = Course::where('college_id', $college->id)->orderBy('name')->get();
        $courseId = $request->integer('course');
        if ($courseId && ! $courses->contains('id', $courseId)) {
            $courseId = 0;
        }

        $category = $request->query('category', '');
        if (! in_array($category, ['', 'Shirts', 'Jackets', 'Accessories'], true)) {
            $category = '';
        }

        $products = Product::with(['college', 'course', 'sizes'])
            ->where('college_id', $college->id)
            ->when($courseId, fn ($query) => $query->where(fn ($filter) => $filter->where('course_id', $courseId)->orWhereNull('course_id')))
            ->when($category, fn ($query) => $query->where('category', $category))
            ->latest('id')->get();

        return view('store.college', compact('college', 'courses', 'courseId', 'category', 'products'));
    }

    public function product(Product $product)
    {
        abort_unless(in_array($product->college()->value('code'), self::COLLEGE_CODES, true), 404);
        $product->load(['college', 'course', 'sizes']);

        return view('store.product', compact('product'));
    }
}
