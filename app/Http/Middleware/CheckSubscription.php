<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
       
        $user = Auth::user();
  
        if (!$user) {
            Log::error('User not authenticated');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::info('Authenticated user', ['user' => $user]);

        if ($user->role !== 'owner') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->subscription_type == 'none' || now()->greaterThan($user->subscription_end_date)) {
            return response()->json(['error' => 'يجب أن تشترك في باقات الخدمة لتتمكن من إدراج ملعبك'], 403);
        }

        if ($user->subscription_type == 'normal' && $user->playgrounds()->count() >= 1) {
            return response()->json(['error' => 'صاحب الملعب المشترك في باقة خدمة يستطيع فقط إضافة ملعب واحد'], 403);
        }

        return $next($request);
    }
}
