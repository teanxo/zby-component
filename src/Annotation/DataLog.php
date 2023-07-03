<?php

namespace Hyperf\Zby\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 日志表
 */
#[Attribute(Attribute::TARGET_METHOD)]
class DataLog extends AbstractAnnotation
{
    public function __construct(
        public string $menuName,
    ){}
}