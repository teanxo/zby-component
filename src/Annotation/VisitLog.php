<?php

namespace Hyperf\Zby\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 日志表
 */
#[Attribute(Attribute::TARGET_METHOD)]
class VisitLog extends AbstractAnnotation
{
    public function __construct(
        public string $menuName,
    ){}
}