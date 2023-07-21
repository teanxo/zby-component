<?php
namespace Hyperf\Zby\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;


/**
 * 数据库字段信息
 */
#[Constants]
class UserIdFieldConstants extends AbstractConstants
{
    /**
     * @Message("老数据库用户ID字段")
     */
    const OID = "oid";

    /**
     * @Message("新数据库用户ID字段")
     */
    const UUID = "uuid";

    public static function getDefaultId()
    {
        return self::OID;
    }
}