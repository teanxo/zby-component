<?php

namespace Hyperf\Zby\Listener;

use Hyperf\DbConnection\Db;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Zby\Event\Operation;
use Psr\Container\ContainerInterface;

/**
 * 操作事件监听器
 */
#[Listener]
class OperationListener implements ListenerInterface
{

    public function __construct(
        public ContainerInterface $container
    )
    {
    }

    public function listen(): array
    {
        return [Operation::class];
    }

    public function process(object $event): void
    {
        $requestInfo = $event->requestInfo;
        $requestInfo['request_data'] = json_encode($requestInfo['request_data'], JSON_UNESCAPED_UNICODE);
        Db::table('operation_log')->insert($requestInfo);
    }
}