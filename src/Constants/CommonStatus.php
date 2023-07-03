<?php

namespace Hyperf\Zby\Constants;


use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

#[Constants]
class CommonStatus extends AbstractConstants
{
    /**
     * @Message("禁用")
     */
    const DISABLE = 0;

    /**
     * @Message("已启用")
     */
    const ENABLE = 1;

    public static function has($value)
    {
        $clazz = new \ReflectionClass(self::class);
        foreach ($clazz->getConstants() as $key => $val){
            if ($val == $value) {
                return true;
            }
        }
        return false;
    }
}