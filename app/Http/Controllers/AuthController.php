<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthFormRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;


class AuthController extends Controller
{

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function user_Register(AuthFormRequest $request): JsonResponse
    {
        $result = $this->authService->user_Register($request->validated());

        return response()->json([
            'message' => 'تم إنشاء حساب المستخدم بنجاح',
        ]);
    }

    public function send_email(AuthFormRequest $request): JsonResponse
    {
        $email = $request->validated()['email'];
        $this->authService->send_email($email);

        return response()->json([
            'message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني، يرجى التحقق منه'
        ]);
    }

    public function verification(AuthFormRequest $request): JsonResponse
    {
        $success = $this->authService->verification($request->validated());
        if (!$success) {
            return response()->json([
                'message' => 'رمز التحقق غير صحيح، يرجى المحاولة مرة أخرى'
            ], 400);
        }
        return response()->json([
            'message' => 'تم التحقق من بريدك الإلكتروني بنجاح',
            'token' => $success['token'] ?? null ,
        ], 200);
    }

    // public function new_password_verification(AuthFormRequest $request): JsonResponse
    // {
    //     $success = $this->authService->new_password_verification($request->validated());
    //     if (!$success) {
    //         return response()->json([
    //             'message' => 'رمز التحقق غير صحيح، يرجى المحاولة مرة أخرى'
    //         ], 400);
    //     }
    //     return response()->json([
    //         'message' => 'تم التحقق من بريدك الإلكتروني بنجاح',
    //         'reset_token' => $success['reset_token'] ?? null
    //     ], 200);
    // }

    public function reset_password(AuthFormRequest $request): JsonResponse
    {
        $success = $this->authService->reset_password($request->validated());
        if (!$success) {
            return response()->json([
                'message' => 'رمز إعادة التعيين غير صالح أو منتهي'
            ], 401);
        }
        return response()->json([
            'message' => 'تم تغيير كلمة المرور بنجاح'
        ]);
    }


    public function login(AuthFormRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if ($result === 'unverified') {
            return response()->json(['message' => 'يجب عليك تأكيد بريدك الإلكتروني أولاً'],202);
        }
        if ($result === 'banned') {
            return response()->json(['message' => 'تم حظر هذا الحساب، يرجى التواصل مع الإدارة'],403);
        }
        if (!$result) {
            return response()->json(['message' => 
                'البريد الإلكتروني لا يتطابق مع كلمة المرور، يرجى المحاولة مرة أخرى'],401);
        }

        return response()->json($result, 200);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    }
  
}
