<?php

declare(strict_types=1);

namespace HelloCoop\Utils;

/**
 * Constrains request-supplied redirect targets before they reach a
 * Location header.
 */
final class RedirectUri
{
    /**
     * Returns $uri when it is an absolute http(s) URL or an absolute path,
     * otherwise $fallback.
     *
     * Rejected: other schemes (javascript:, data:, ...), the scheme-relative
     * "//host" and "/\host" forms that browsers resolve to a new origin, and
     * anything containing whitespace or control characters.
     *
     * The host of an absolute URL is deliberately not restricted. The
     * reference @hellocoop/api implementation accepts any target_uri and
     * applications rely on cross-host targets.
     *
     * @psalm-taint-escape header
     */
    public static function sanitize(mixed $uri, string $fallback): string
    {
        if (!is_string($uri) || $uri === '') {
            return $fallback;
        }

        if (preg_match('/[\x00-\x20\x7F]/', $uri) === 1) {
            return $fallback;
        }

        if ($uri[0] === '/') {
            $second = $uri[1] ?? '';
            return ($second === '/' || $second === '\\') ? $fallback : $uri;
        }

        $parts = parse_url($uri);
        if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
            return $fallback;
        }

        $scheme = strtolower($parts['scheme']);
        return ($scheme === 'http' || $scheme === 'https') ? $uri : $fallback;
    }
}
