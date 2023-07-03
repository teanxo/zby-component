<?php

namespace Hyperf\Zby\Annotation\Validation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_METHOD)]
class Email extends Validator
{
    public function __construct(string $message = '', string $group = '')
    {
        $this->message = $message;
        $this->group = $group;
    }
}