<?php

namespace App\Http\Controllers\LOGIN;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index()
    {
        return view('login/login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required'],
            'password' => ['required']
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->level == '1') {
                return redirect()->intended('/IT');
            } elseif ($user->level == '2') {
                return redirect()->intended('/WH');
            } elseif ($user->level == '3') {
                return redirect()->intended('/HO');
            }
            return redirect('/');
        }
        return redirect('/')->with('LoginError', 'Akun atau Password Salah');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        Auth::logout();
        return redirect('/');
    }
}
