<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaksaGantiPassword
{
    /**
     * Route yang tetap boleh diakses walau user masih wajib mengganti
     * password sementara: halaman profil, update password, dan logout.
     */
    private const BOLEH = [
        'profile.show',
        'profile.password.update',
        'profile.info.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->wajib_ganti_password
            && ! in_array($request->route()?->getName(), self::BOLEH, true)) {
            return redirect()->to(route('profile.show').'#ganti-password')
                ->with('success', 'Silakan ganti password sementara Anda terlebih dahulu.');
        }

        return $next($request);
    }
}
