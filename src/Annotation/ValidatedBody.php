<?php

namespace Hyperf\Zby\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

use Attribute;

/**
 * 实体类校验解析
 * @description 方法中存在该注解 且 方法参数中存在类对象时，那么对方法实体类对象参数的属性进行扫描
 * @description 若方法中的类对象参数属性 存在需要Validation下的注解时，进行解析校验
 * @description 该注解只对方法中存在实体类对象的参数进行解析，其它参数不进行解析
 * @author taxcode
 * @email 483586199@qq.com
 */
#[Attribute(Attribute::TARGET_METHOD)]
class ValidatedBody extends AbstractAnnotation
{
    public function __construct(
        public string $group = ''
    )
    {
    }
}