<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountActive
{
    /**
     * Handle an incoming request.
     *
     * تحقق من أن حساب المستخدم نشط (is_active = true)
     * إذا كان معطل، يرجع 403 Forbidden
     */
    public function handle(Request $request, Closure $next): Response
    {
        // التحقق من المستخدم المسجل دخول حالياً
        $user = $request->user();

        // إذا كان هناك مستخدم وحسابه معطل
        if ($user && !$user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'هذا الحساب معطل حالياً، يرجى مراجعة المسؤول',
                'code' => 'ACCOUNT_SUSPENDED'
            ], 403);
        }

        return $next($request);
    }
}
