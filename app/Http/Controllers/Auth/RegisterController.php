<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Helper;
use App\User;
use App\Utils\APIResponse;
use Illuminate\Http\Request;

class RegisterController
{
    public function register(Request $request)
    {
        $request->validate([
            'email' => ['required', 'unique:users', 'email'],
            'name' => ['required', 'unique:users', 'string', 'max:24', 'regex:/^[a-zA-Z0-9]{5,24}$/u'],
            'password' => ['required', 'string', 'min:5'],
            'captcha' => ['required'],
        ]);

        if (! Helper::validateCaptcha($request->captcha)) {
            return APIResponse::reject(2, 'Invalid captcha');
        }

        $user = Helper::createUser($request->email, $request->name, $request->password);

        return APIResponse::success([
            'user' => $user->toArray(),
            'token' => $user->createToken()->plainTextToken,
        ]);
    }
}
