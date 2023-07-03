<?php

namespace Hyperf\Zby\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

use Annotation;
use Attribute;
use Hyperf\Zby\Annotation\Collector\RequestBodyCollector;

/**
 * 实体类解析
 * @Annotation
 * @Target({"PARAMETER"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class RequestBody extends AbstractAnnotation
{
    public function __construct(
        public string $clazz
    )
    {

    }
}