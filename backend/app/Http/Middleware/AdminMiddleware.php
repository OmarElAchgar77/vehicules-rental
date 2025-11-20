<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Pour le moment, on considère que tous les utilisateurs authentifiés sont admin
        // Dans un vrai projet, vous auriez une colonne 'is_admin' dans la table users
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ici vous pouvez vérifier si l'utilisateur est admin
        if (!$request->user()->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}