<?php

namespace Hyperf\Zby\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Attribute;

/**
 * 自定义验证类
 * @author taxcode
 * @email 483586199@qq.com
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_METHOD)]
class Valid extends AbstractAnnotation
{
    public function __construct(
        public string|array $rule,
        public string|array $message,
        public mixed $scene = null
    )
    {
    }
}