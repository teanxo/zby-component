<?php

namespace Hyperf\Zby\Annotation\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_METHOD)]
class Mobile extends Validator
{
    public function __construct(string $message = '', string $group = '', public string $country = 'CN')
    {
        $this->message = $message;
        $this->group = $group;
    }
}