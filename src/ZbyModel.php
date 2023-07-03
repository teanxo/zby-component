<?php

namespace Hyperf\Zby;

use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Zby\Traits\ModelMacroTrait;


class ZbyModel extends Model
{


    use ModelMacroTrait;

    /**
     * 需要隐藏的字段
     * @var array|string[]
     */
    protected array $hidden = ['deleted_at'];

    /**
     * 表名称
     * @var string|null
     */
    public ?string $table;

    /**
     * 模型主键
     * @var string
     */
    public string $primaryKey = 'id';


    /**
     * 默认每页记录数
     */
    public const PAGE_SIZE = 15;

    /**
     * 默认状态启用值
     */
    public const ENABLE = 1;

    /**
     * 默认状态禁用值
     */
    public const DISABLE = 0;

    /**
     * 是否开启自动注入用户信息
     * @var bool
     */
    public bool $auto_by_user = false;

    /**
     * 默认创建人字段
     * @var string
     */
    public string $created_column = 'created_by';

    public string $updated_column = 'updated_by';

    public string $deleted_column = 'deleted_by';



    /**
     * 创建人合并显示字段
     * @var string
     */
    public string $created_field = 'created';

    public string $updated_field = 'updated';

    public string $deleted_field = 'deleted';


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->registerBase();
        $this->registerUserDataScope();
    }

}