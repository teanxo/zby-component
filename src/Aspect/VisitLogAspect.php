<?php

namespace Hyperf\Zby\Aspect;

use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Zby\Annotation\VisitLog;
use Hyperf\Zby\Event\VisitLog;
use Hyperf\Zby\Helper\LoginUser;
use Hyperf\Zby\Helper\Str;
use Hyperf\Zby\ZbyRequest;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;

#[Aspect]
class VisitLogAspect extends AbstractAspect
{
    public array $annotations = [
        VisitLog::class
    ];

    protected ContainerInterface $container;

    public function __construct()
    {
        $this->container = ApplicationContext::getContainer();
    }


    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $annotation = $proceedingJoinPoint->getAnnotationMetadata()->method[VisitLog::class];
        /* @var $result ResponseInterface */
        $result = $proceedingJoinPoint->process();

        if (!empty($annotation->menuName)) {
            // 触发事件调度器
            $evDispatcher = $this->container->get(EventDispatcherInterface::class);
            $evDispatcher->dispatch(new VisitLog($this->getRequestInfo([
                'name' => $annotation->menuName,
                'response_code' => $result->getStatusCode(),
                'response_data' => $result->getBody()->getContents()
            ])));
        }

        return $result;
    }

    protected function getRequestInfo(array $data): array
    {
        $request = $this->container->get(ZbyRequest::class);
        $loginUser = $this->container->get(LoginUser::class);

        $operationLog = [
            'time' => date('Y-m-d H:i:s', $request->getServerParams()['request_time']),
            'method' => $request->getServerParams()['request_method'],
            'router' => $request->getServerParams()['path_info'],
            'protocol' => $request->getServerParams()['server_protocol'],
            'ip' => $request->ip(),
            'ip_location' => Str::ipToRegion($request->ip()),
            'menu_name' => $data['name'],
            'request_data' => $request->all(),
            'response_code' => $data['response_code'],
            'response_data' => $data['response_data']
        ];

        try{
            $operationLog['userid'] = $loginUser->getId();
        }catch (\Exception $e){

        }

        return $operationLog;
    }
}