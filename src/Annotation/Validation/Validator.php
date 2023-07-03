<?php

namespace Hyperf\Zby\Annotation\Validation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_METHOD)]
abstract class Validator extends AbstractAnnotation
{
    public function __construct(
        public string $message = '',
        public string $group = ''
    )
    {

    }
}