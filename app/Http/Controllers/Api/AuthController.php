<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Dùng lại LoginRequest của web: cùng rule, cùng giới hạn số lần sai, cùng log auth.login.
     * Nhóm api không có StartSession nên guard web chỉ kiểm tra mật khẩu, không lưu session.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        return response()->json(['token' => $request->user()->createToken('api')->plainTextToken]);
    }
}
