<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserStatusMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user->status == 2) {
            return response()->json([
                'message' => 'تم تجميد حسابك، لا يمكنك القيام بهذه المهمة.. يرجى دفع التكاليف المترتبة عليك.'
            ], 403);
        }
        return $next($request);
    }
}
