<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

class VerifyCsrfToken extends Middleware
{
    /**
     * TEMPORARY: Capture safe fingerprints and request routing metadata only
     * when CSRF validation fails. Remove after the intermittent 419 is traced.
     */
    public function handle($request, $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $exception) {
            $fingerprint = static fn ($value) => $value === null || $value === ''
                ? null
                : substr(hash('sha256', (string) $value), 0, 12);

            $sessionCookieName = (string) config('session.cookie');
            $cookieHeader = (string) $request->headers->get('cookie', '');
            $cookiePattern = '/(?:^|;\\s*)' . preg_quote($sessionCookieName, '/') . '=/i';
            preg_match_all($cookiePattern, $cookieHeader, $cookieMatches);

            $safeOrigin = static function ($value) {
                if (!$value) {
                    return null;
                }

                $parts = parse_url($value);
                if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
                    return 'unparseable';
                }

                return strtolower($parts['scheme']) . '://' . strtolower($parts['host'])
                    . (isset($parts['port']) ? ':' . $parts['port'] : '');
            };

            $requestToken = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
            $headerToken = $request->header('X-CSRF-TOKEN');
            $session = $request->hasSession() ? $request->session() : null;

            Log::warning('TEMP CSRF diagnostic: token mismatch', [
                'path' => $request->path(),
                'host' => $request->getHost(),
                'scheme' => $request->getScheme(),
                'port' => $request->getPort(),
                'origin' => $safeOrigin($request->header('Origin')),
                'referer_origin' => $safeOrigin($request->header('Referer')),
                'cookie_names' => array_keys($request->cookies->all()),
                'session_cookie_count_in_header' => count($cookieMatches[0]),
                'session_cookie_fingerprint' => $fingerprint($request->cookie($sessionCookieName)),
                'request_token_fingerprint' => $fingerprint($requestToken),
                'header_token_fingerprint' => $fingerprint($headerToken),
                'xsrf_header_fingerprint' => $fingerprint($request->header('X-XSRF-TOKEN')),
                'session_token_fingerprint' => $fingerprint($session?->token()),
                'session_id_fingerprint' => $fingerprint($session?->getId()),
                'app_key_fingerprint' => $fingerprint(config('app.key')),
                'server_addr' => $request->server('SERVER_ADDR'),
                'server_port' => $request->server('SERVER_PORT'),
                'instance' => gethostname(),
            ]);

            throw $exception;
        }
    }

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
    ];
}
