<?php

namespace Hyperf\Zby\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Zby\Annotation\AuthAnnotation;
use Hyperf\Zby\Exception\TokenException;
use Hyperf\Zby\Helper\LoginUser;

#[Aspect]
class AuthAspect extends AbstractAspect
{
    public array $annotations = [
        AuthAnnotation::class
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $scene = 'default';

        if (isset($proceedingJoinPoint->getAnnotationMetadata()->class[AuthAnnotation::class])){
            $auth = $proceedingJoinPoint->getAnnotationMetadata()->class[AuthAnnotation::class];
            $scene = $auth->scene ?? 'default';
        }

        if (isset($proceedingJoinPoint->getAnnotationMetadata()->method[AuthAnnotation::class])){
            $auth = $proceedingJoinPoint->getAnnotationMetadata()->method[AuthAnnotation::class];
            $scene = $auth->scene ?? 'default';
        }

        $loginUser = new LoginUser();

        if (!$loginUser->check(null, $scene)) {
            throw new TokenException('Token 验证失败，可能已过期或者在黑名单');
        }

        return $proceedingJoinPoint->process();
    }
}