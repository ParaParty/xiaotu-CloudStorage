<?php

namespace App\Http\Controllers;

use App\Models\User;
use http\Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        // 验证表单数据
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:8',
        ]);

        do{
            $id=random_int(10000,99999);
        }while(User::where('id',$id)->first());

        // 创建用户
        $user = User::create([
            'id'=>$id,
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);
        // 执行登录
        Auth::login($user);

        // 跳转到首页或其他页面
        return redirect()->route('view.home');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            // 认证成功，用户登录成功
            $token=$request->user()->createToken("USER_TOKEN")->plainTextToken;

            return redirect()->intended('/home');
        }
        return redirect()->back()->withInput()->withErrors([
            'email' => 'Invalid credentials.',
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->back();
    }
}
