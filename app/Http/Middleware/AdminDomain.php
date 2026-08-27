<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminDomain
{
    /**
     * Jika diakses via domain admin (misal admin.azkia.cloud):
     * - root ("/") diarahkan ke panel admin
     * - semua URL yang digenerate (route, asset) tetap memakai domain admin
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        if (str_starts_with($host, 'admin.')) {
            // Root -> panel admin
            if ($request->path() === '/') {
                return redirect()->to('https://'.$host.'/admin');
            }

            // Force URL generation ke host admin
            app('url')->forceRootUrl('https://'.$host);
        }

        return $next($request);
    }
}
