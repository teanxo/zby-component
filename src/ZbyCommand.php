<?php

namespace Hyperf\Zby;

use Hyperf\Command\Command;

abstract class ZbyCommand extends Command
{

    protected string $module;

    protected CONST CONSOLE_GREEN_BEGIN = "\033[32;5;1m";
    protected CONST CONSOLE_RED_BEGIN = "\033[31;5;1m";
    protected CONST CONSOLE_END = "\033[0m";

    protected function getGreenText($text): string
    {
        return self::CONSOLE_GREEN_BEGIN . $text . self::CONSOLE_END;
    }

    protected function getRedText($text): string
    {
        return self::CONSOLE_RED_BEGIN . $text . self::CONSOLE_END;
    }

    protected function getModulePath(): string
    {
        return BASE_PATH . '/app/' . $this->module . '/Request/';
    }

}