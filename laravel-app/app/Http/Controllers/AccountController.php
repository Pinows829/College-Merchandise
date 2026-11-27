<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    private const COLLEGE_CODES = ['CCICT', 'CAS', 'COE', 'COT', 'COED', 'CME'];

    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:190'],
            'password' => ['required', 'string'],
        ]);

        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'student_id';
        if (! Auth::attempt([$field => $credentials['login'], 'password' => $credentials['password']])) {
            return back()->withErrors(['login' => 'The Student ID/email or password is incorrect.'])->onlyInput('login');
        }

        $request->session()->regenerate();

        return redirect()->intended(auth()->user()->role === 'admin' ? route('admin.dashboard') : route('home'));
    }

    public function registerForm()
    {
        $colleges = College::whereIn('code', self::COLLEGE_CODES)
            ->orderByRaw("FIELD(code, 'CCICT','CAS','COE','COT','COED','CME')")
            ->get();

        return view('auth.register', compact('colleges'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'student_id' => ['required', 'string', 'max:40', 'unique:users,student_id'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'college_id' => ['required', 'integer', 'exists:colleges,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        abort_unless(College::whereKey($validated['college_id'])->whereIn('code', self::COLLEGE_CODES)->exists(), 422);

        User::create([
            'full_name' => $validated['full_name'],
            'student_id' => $validated['student_id'],
            'email' => $validated['email'],
            'college_id' => $validated['college_id'],
            'password' => Hash::make($validated['password']),
            'role' => 'student',
        ]);

        return redirect()->route('login')->with('success', 'Account created. You can now sign in.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function orders()
    {
        abort_unless(auth()->user()->role === 'student', 403);
        $orders = Order::with('items.productSize.product')->where('user_id', auth()->id())->latest('id')->get();

        return view('account.orders', compact('orders'));
    }
}
