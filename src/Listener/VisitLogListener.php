<?php

namespace Hyperf\Zby\Listener;

use Hyperf\DbConnection\Db;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Zby\Event\VisitLogEvent;
use Psr\Container\ContainerInterface;

/**
 * 操作事件监听器
 */
#[Listener]
class VisitLogListener implements ListenerInterface
{

    public function __construct(
        public ContainerInterface $container
    )
    {
    }

    public function listen(): array
    {
        return [VisitLogEvent::class];
    }

    public function process(object $event): void
    {
        $requestInfo = $event->requestInfo;
        $requestInfo['request_data'] = json_encode($requestInfo['request_data'], JSON_UNESCAPED_UNICODE);
        Db::table('visit_log')->insert($requestInfo);
    }
}