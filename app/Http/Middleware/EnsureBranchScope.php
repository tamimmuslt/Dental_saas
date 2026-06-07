<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchScope
{
    /**
     * Handle an incoming request.
     *
     * Middleware للـ Multi-Tenancy
     * يحقن branch_id تلقائياً في الـ queries إذا لم يكن المستخدم super_admin
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role !== 'super_admin' && $user->branch_id) {
            $request->merge(['current_branch_id' => $user->branch_id]);
        }

        return $next($request);
    }
}
