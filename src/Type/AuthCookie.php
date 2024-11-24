<?php

namespace HelloCoop\Type;

// Authentication cookie class, extending Claims
class AuthCookie extends Claims {
    /** @var int */
    public $iat;

    /**
     * @var array<string, mixed>
     * Allow arbitrary optional properties.
     */
    public $extraProperties = [];

    public function __construct(string $sub, int $iat) {
        parent::__construct($sub);
        $this->iat = $iat;
    }

    /**
     * Add an extra property.
     */
    public function setExtraProperty(string $key, $value): void {
        $this->extraProperties[$key] = $value;
    }

    /**
     * Get an extra property.
     */
    public function getExtraProperty(string $key) {
        return $this->extraProperties[$key] ?? null;
    }
}