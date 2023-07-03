<?php

namespace Hyperf\Zby\Aspect;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\Codec\Json;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Zby\Annotation\Valid;
use Hyperf\Zby\Annotation\ValidatedBody;
use Hyperf\Zby\Annotation\Validation\Constant;
use Hyperf\Zby\Annotation\Validation\Email;
use Hyperf\Zby\Annotation\Validation\IdCard;
use Hyperf\Zby\Annotation\Validation\Max;
use Hyperf\Zby\Annotation\Validation\Min;
use Hyperf\Zby\Annotation\Validation\Mobile;
use Hyperf\Zby\Annotation\Validation\Required;
use Hyperf\Zby\Helper\ResponseCode;
use Hyperf\Zby\Traits\ValidationTrait;

#[Aspect]
class ValidationAspect extends AbstractAspect
{
    public array $annotations = [
        ValidatedBody::class,
        Email::class,
        IdCard::class,
        Max::class,
        Min::class,
        Mobile::class,
        Required::class,
    ];


    public array $toMap = [
        Email::class,
        IdCard::class,
        Max::class,
        Min::class,
        Mobile::class,
        Required::class,
    ];

    #[Inject]
    protected ValidatorFactoryInterface $validatorFactory;

    use ValidationTrait;

    public function __construct(
        public RequestInterface $request,
        public ResponseInterface $response
    )
    {

    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $this->validatedBody($proceedingJoinPoint);

        $validator = $this->validatorFactory->make(
            $this->request->all(),
            $this->rules,
            $this->messages
        );

        if ($validator->fails()){
            return $this->responseHandler($validator->errors()->first());
        }
        return $proceedingJoinPoint->process();
    }

    /**
     * 实体类注解解析
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return void
     */
    public function validatedBody(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $methodAnnotations = AnnotationCollector::getClassMethodAnnotation($proceedingJoinPoint->className, $proceedingJoinPoint->methodName);
        if (isset($methodAnnotations[ValidatedBody::class])){

            $group = $methodAnnotations[ValidatedBody::class]->group;

            $reflectionMethod = new \ReflectionMethod($proceedingJoinPoint->className, $proceedingJoinPoint->methodName);
            $parameters = $reflectionMethod->getParameters();

            foreach ($parameters as $parameter){
                if (is_object($parameter)){

                    $reflectionClass = new \ReflectionClass($parameter->getType()->getName());
                    $properties = $reflectionClass->getProperties();
                    foreach ($properties as $property) {
                        $propertyAnnotations = AnnotationCollector::getClassPropertyAnnotation($parameter->getType()->getName(), $property->getName());
                        foreach ($this->toMap as $ruleAnnotation){
                            if (isset($propertyAnnotations[$ruleAnnotation])) {
                                $annotaionRefClass = new \ReflectionClass($ruleAnnotation);
                                $annotationShortName = $annotaionRefClass->getShortName();
                                $this->$annotationShortName($property, $propertyAnnotations[$ruleAnnotation]);
                            }
                        }
                    }
                }
            }

            if (!empty($group)){
                // 获取现在所有的规则
                $rules = $this->rules;

                // 将规则表设置为空
                $this->rules = [];

                foreach($rules as $property => $rule_str) {
                    // 如果满足当前的分组条件
                    if (isset($this->groups[$group]) && !empty($this->groups[$group][$property])){
                        // 循环添加进规则
                        $property_rules = $this->groups[$group][$property];
                        foreach (explode('|', $rule_str) as $rule){
                            if (in_array($rule, $property_rules)) {
                                $this->setValidatorRule($property, $rule);
                            }
                        }
                    }
                }
                foreach($this->notGroups as $property => $rs){
                    foreach ($rs as $r){
                        $this->setValidatorRule($property, $r);
                    }
                }

            }
        }
    }


    /**
     * 响应数据
     * @param $errorMessage
     * @return mixed
     */
    public function responseHandler($errorMessage)
    {
        $format = [
            'success' => false,
            'message' => $errorMessage,
            'code'    => ResponseCode::VALIDATE_FAILED,
        ];
        return $this->response->withHeader('Server', 'MineAdmin')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods','GET,PUT,POST,DELETE,OPTIONS')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Headers', 'accept-language,authorization,lang,uid,token,Keep-Alive,User-Agent,Cache-Control,Content-Type')
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withStatus(200)->withBody(new SwooleStream(Json::encode($format)));
    }

}