<?php

namespace Hyperf\Zby\Aspect;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Zby\Annotation\RequestBody;

#[Aspect]
class RequestBodyAspect extends AbstractAspect
{

    public array $annotations = [
        RequestBody::class
    ];

    public function __construct(
        public RequestInterface $request
    )
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $annotations = AnnotationCollector::getClassMethodAnnotation($proceedingJoinPoint->className, $proceedingJoinPoint->methodName);

        if (isset($annotations[RequestBody::class])){
            // new procedingJoin reflect class
            $reflectionMethod = new \ReflectionMethod($proceedingJoinPoint->className, $proceedingJoinPoint->methodName);
            $parameters = $reflectionMethod->getParameters();
            foreach($parameters as $parameter){
                $clazz = $annotations[RequestBody::class]->clazz;
                if ($clazz == $parameter->getType()->getName()){
                    $reflectionClass = new \ReflectionClass($clazz);
                    $properties = $reflectionClass->getProperties();

                    $instance = $reflectionClass->newInstance();
                    foreach ($properties as $property){
                        $val = $this->request->input($property->getName(), '');
                        $property->setAccessible(true);
                        $property->setValue($instance, $val);
                    }

                    $key = $parameter->getName();
                    $proceedingJoinPoint->arguments['keys'][$key] = $instance;
                    $index = array_search($key, $proceedingJoinPoint->arguments['order']);
                    if ($index !== false) {
                        $proceedingJoinPoint->arguments['order'][$index] = $instance;
                    }
                }
            }
        }

        return $proceedingJoinPoint->process();
    }
}