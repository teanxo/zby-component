<?php

namespace Hyperf\Zby\Aspect;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Zby\Annotation\RequestParamter;

#[Aspect]
class RequestParamterAspect extends AbstractAspect
{

    public array $annotations = [
        RequestParamter::class
    ];

    public function __construct(
        public RequestInterface $request
    )
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $annotations = AnnotationCollector::getClassMethodAnnotation($proceedingJoinPoint->className, $proceedingJoinPoint->methodName);
        if (isset($annotations[RequestParamter::class])){
            $reflectionMethod = new \ReflectionMethod($proceedingJoinPoint->className, $proceedingJoinPoint->methodName);
            $parameters = $reflectionMethod->getParameters();
            foreach($parameters as $parameter){
                $parameterName = $annotations[RequestParamter::class]->name;
                if ($parameterName == $parameter->getName()) {
                    $val = $this->request->input($parameterName, '');

                    $key = $parameter->getName();
                    $proceedingJoinPoint->arguments['keys'][$key] = $val;
                    $index = array_search($key, $proceedingJoinPoint->arguments['order']);
                    if ($index !== false) {
                        $proceedingJoinPoint->arguments['order'][$index] = $val;
                    }
                }
            }
        }
        return $proceedingJoinPoint->process();
    }
}