<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Clear setting cache sekali per session agar perubahan setting admin
 * langsung terlihat saat user membuka aplikasi.
 */
class RefreshSettingsCache
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->missing('settings_cache_cleared')) {
            Setting::clearCache();
            $request->session()->put('settings_cache_cleared', true);
        }

        return $next($request);
    }
}
