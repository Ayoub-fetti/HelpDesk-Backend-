<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleFormDataRequest
{
    public function handle(Request $request, Closure $next)
    {
        // Vérifie si c'est une requête form-data
        if ($request->isMethod('put') || $request->isMethod('patch')) {
            $contentType = $request->header('Content-Type', '');
            
            if (strpos($contentType, 'multipart/form-data') !== false) {
                // Nettoie les données form-data
                $input = $request->all();
                
                foreach ($input as $key => $value) {
                    // Convertit les chaînes vides en null
                    if ($value === '' || $value === 'null' || $value === 'undefined') {
                        $input[$key] = null;
                    }
                    
                    // Convertit les chaînes numériques en entiers
                    if (is_string($value) && is_numeric($value) && strpos($value, '.') === false) {
                        $input[$key] = (int) $value;
                    }
                }
                
                $request->replace($input);
            }
        }
        
        return $next($request);
    }
}