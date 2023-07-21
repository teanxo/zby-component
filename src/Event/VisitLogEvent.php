<?php

declare(strict_types=1);

namespace Hyperf\Zby\Event;

/**
 * 提供全局访问日志事件
 */
class VisitLogEvent
{
    public function __construct(
        public array $requestInfo
    )
    {
    }
}