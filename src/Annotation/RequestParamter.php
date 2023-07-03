<?php

namespace Hyperf\Zby\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RequestParamter  extends AbstractAnnotation
{
    public function __construct(
        public string $name
    )
    {
    }
}