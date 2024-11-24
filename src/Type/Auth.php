<?php

namespace HelloCoop\Type;

class Auth {
    /** @var bool */
    public $isLoggedIn;

    /** @var string|null */
    public $cookieToken;

    /** @var AuthCookie|null */
    public $authCookie;

    public function __construct(bool $isLoggedIn, ?AuthCookie $authCookie = null, ?string $cookieToken = null) {
        $this->isLoggedIn = $isLoggedIn;
        $this->authCookie = $authCookie;
        $this->cookieToken = $cookieToken;
    }
}

/**
* $authCookie = new AuthCookie('user123', time());
* $authCookie->setExtraProperty('role', 'admin');
* $auth = new Auth(true, $authCookie, 'token123');

* echo "User is logged in: " . ($auth->isLoggedIn ? 'Yes' : 'No') . PHP_EOL;
* echo "User role: " . $auth->authCookie->getExtraProperty('role') . PHP_EOL;
 */
