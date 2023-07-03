<?php

declare(strict_types=1);

namespace Hyperf\Zby\Event;


class Operation
{
    public function __construct(
        public array $requestInfo
    )
    {
    }
}