<?php

declare(strict_types = 1);

namespace Hyperf\Zby\Annotation\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_METHOD)]
class Required extends Validator
{
    public function __construct(string $message, string $group = '')
    {
        $this->message = $message;
        $this->group = $group;
    }

}