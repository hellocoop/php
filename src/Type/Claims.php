<?php

namespace HelloCoop\Type;

use HelloCoop\Type\Common\OptionalStringClaimsTrait;
use HelloCoop\Type\Common\OptionalAccountClaimsTrait;
use HelloCoop\Type\Common\OptionalOrgClaimTrait;

class Claims {
    use OptionalStringClaimsTrait, OptionalAccountClaimsTrait, OptionalOrgClaimTrait;

    /** @var string */
    public $sub;

    public function __construct(string $sub) {
        $this->sub = $sub;
    }
}