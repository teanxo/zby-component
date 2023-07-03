<?php

namespace Hyperf\Zby\Annotation\Validation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_METHOD)]
class Min extends AbstractAnnotation
{

}