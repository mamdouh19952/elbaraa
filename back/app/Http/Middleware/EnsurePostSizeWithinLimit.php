<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePostSizeWithinLimit
{
    /**
     * When a request body exceeds `post_max_size`, PHP silently empties
     * $_POST/$_FILES before Laravel ever runs — the framework has no
     * exception to catch. Content-Length survives (only the body is
     * dropped), so it's the only signal available to turn that silent
     * failure into a real error instead of a broken/empty request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
        $maxBytes = $this->iniSizeToBytes((string) ini_get('post_max_size'));

        if ($maxBytes > 0 && $contentLength > $maxBytes) {
            return response()->json([
                'status' => false,
                'message' => 'Upload is too large. Please use a smaller file.',
            ], 413);
        }

        return $next($request);
    }

    private function iniSizeToBytes(string $size): int
    {
        $value = (int) $size;
        $unit = strtolower(substr(trim($size), -1));

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }
}
