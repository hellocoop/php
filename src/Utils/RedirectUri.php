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
     * Taint kinds, as Psalm 7.16 models a Location header and an href:
     * - header, url_scheme: enforced here. No control characters, and only
     *   http, https or an absolute path, so javascript: never reaches an
     *   href.
     * - redirect, url_component, url_path: accepted by design. The caller
     *   supplies the whole target, host included (see above), so its path
     *   and query are the caller's to choose as well.
     *
     * @psalm-taint-escape header
     * @psalm-taint-escape url_scheme
     * @psalm-taint-escape redirect
     * @psalm-taint-escape url_component
     * @psalm-taint-escape url_path
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
