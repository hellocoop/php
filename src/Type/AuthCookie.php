<?php

namespace HelloCoop\Type;

use InvalidArgumentException;

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

    /**
     * Create an instance from an array of key-value pairs.
     */
    public static function fromArray(array $data): self {
        if (!isset($data['sub'], $data['iat'])) {
            throw new InvalidArgumentException('Missing required keys "sub" or "iat".');
        }

        $instance = new self($data['sub'], $data['iat']);

        foreach ($data as $key => $value) {
            if (!in_array($key, ['sub', 'iat'], true)) {
                $instance->setExtraProperty($key, $value);
            }
        }

        return $instance;
    }
}