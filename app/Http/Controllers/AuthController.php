<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthFormRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $result = $this->authService->send_email($email);

        if (!$result) {
            return response()->json(['message' => 
                'البريد الإلكتروني غير صحيح، يرجى المحاولة مرة أخرى'],401);
        }

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
        $response = [
            'message' => 'تم التحقق من بريدك الإلكتروني بنجاح',
            'token' => $success['token'] ?? null ,
            'refresh_token' => $success['refresh_token'] ?? null
        ];

        $remember = filter_var(
            $request->header('X-Remember-Me'),
            FILTER_VALIDATE_BOOLEAN
        );

        $isWeb = $request->hasHeader('X-Remember-Me');
         // Mobile
        if (!$isWeb || $response['refresh_token'] == null) {
            return response()->json($response);
        }
        // Web
        $minutes = $remember ? 60 * 24 * 30 : 60 * 24;

        $cookie = cookie(
            'refresh_token', $response['refresh_token'], $minutes, '/', null, true, true, false, 'Strict'
        );

        return response()->json($response)->withCookie($cookie);
    }

    public function new_password_verification(AuthFormRequest $request): JsonResponse
    {
        $success = $this->authService->new_password_verification($request->validated());
        if (!$success) {
            return response()->json([
                'message' => 'رمز التحقق غير صحيح، يرجى المحاولة مرة أخرى'
            ], 400);
        }
        return response()->json([
            'message' => 'تم التحقق من بريدك الإلكتروني بنجاح',
            'reset_token' => $success['reset_token'] ?? null
        ], 200);
    }

    public function reset_password(AuthFormRequest $request): JsonResponse
    {
        $success = $this->authService->reset_password($request->validated());
        if (!$success) {
            return response()->json([
                'message' => 'رمز إعادة التعيين غير صالح أو منتهي'
            ], 401);
        }
        if ($success === 'same_old_password') {
            return response()->json(['message' => 'الرجاء تغيير كلمة المرور لكي تكون مختلفة عن كلمة المرور القديمة'],401);
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
        if ($result === 'frozen') {
            return response()->json(['message' => 'تم تجميد هذا الحساب، يرجى دفع التكاليف المترتبة عليك'],403);
        }
        if (!$result) {
            return response()->json(['message' => 
                'البريد الإلكتروني لا يتطابق مع كلمة المرور، يرجى المحاولة مرة أخرى'],401);
        }
        $remember = filter_var(
            $request->header('X-Remember-Me'),
            FILTER_VALIDATE_BOOLEAN
        );

        $isWeb = $request->hasHeader('X-Remember-Me');
         // Mobile
        if (!$isWeb) {
            return response()->json($result);
        }
        // Web
        $minutes = $remember ? 60 * 24 * 30 : 60 * 24;

        $cookie = cookie(
            'refresh_token', $result['refresh_token'], $minutes, '/', null, true, true, false, 'Strict'
        );

        return response()->json($result)->withCookie($cookie);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    }

    public function refresh(Request $request)
    {
        $isWeb = $request->hasHeader('X-Remember-Me');

        $refreshToken = $isWeb
            ? $request->cookie('refresh_token')
            : $request->input('refresh_token');

        $result = $this->authService->refresh($refreshToken);

        if ($result === 'banned') {
            return response()->json(['message' => 'banned'], 403);
        }

        if ($result === 'frozen') {
            return response()->json(['message' => 'frozen'], 403);
        }

        if (!$result) {
            return response()->json(['message' => 'Invalid refresh token'], 401);
        }

        // Mobile
        if (!$isWeb) {
            return response()->json($result);
        }

        // Web
        if (isset($result['refresh_token'])) {
            $minutes = 60 * 24 * 30;
            $cookie = cookie(
                'refresh_token', $result['refresh_token'], $minutes, '/', null, true, true, false, 'Strict'
            );

            return response()->json($result)->withCookie($cookie);
        }
    }
  
}
