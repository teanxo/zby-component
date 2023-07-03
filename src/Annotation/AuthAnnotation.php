<?php

declare(strict_types = 1);

namespace Hyperf\Zby\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Annotation;
use Attribute;
/**
 * 登陆验权注解
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class AuthAnnotation extends AbstractAnnotation
{
    /**
     * 应用场景
     * @var string
     */
    public string $scene;

    public function __construct($value = 'default')
    {
        parent::__construct($value);
        $this->bindMainProperty('scene', [ $value ]);
    }
}