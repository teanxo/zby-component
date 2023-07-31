<?php

namespace Hyperf\Zby\Traits;

use App\System\Model\MenuModel;
use Hyperf\Collection\Collection;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Schema\Schema;
use Hyperf\ModelCache\Manager;
use Hyperf\Zby\Exception\NormalStatusException;
use Hyperf\Zby\Helper\Str;
use Hyperf\Zby\Interfaces\ZbyUserInterface;
use Hyperf\Zby\ZbyCollection;
use Hyperf\Zby\ZbyModel;


trait ModelMacroTrait
{

    /**
     * 用户数据权限自定义方法
     * @return void
     */
    private function registerUserDataScope()
    {
        Builder::macro('userDataScope', function (?int $userid = null){});
    }


    /**
     * Description:注册常用自定义方法
     * User:mike
     */
    private function registerBase()
    {
        //添加andFilterWhere()方法
        Builder::macro('andFilterWhere', function ($key, $operator, $value = NULL) {
            if ($value === '' || $value === '%%' || $value === '%') {
                return $this;
            }
            if ($operator === '' || $operator === '%%' || $operator === '%') {
                return $this;
            }
            if($value === NULL){
                return $this->where($key, $operator);
            }else{
                return $this->where($key, $operator, $value);
            }
        });

        //添加orFilterWhere()方法
        Builder::macro('orFilterWhere', function ($key, $operator, $value = NULL) {
            if ($value === '' || $value === '%%' || $value === '%') {
                return $this;
            }
            if ($operator === '' || $operator === '%%' || $operator === '%') {
                return $this;
            }
            if($value === NULL){
                return $this->orWhere($key, $operator);
            }else{
                return $this->orWhere($key, $operator, $value);
            }
        });
    }

    public function import(string $dto, ?\Closure $closure = null): bool
    {
        return (new ZbyCollection())->import($dto, $this, $closure);
    }


    /**
     * 过滤新增或写入不存在的字段
     * @param array $data
     * @param bool $removePk
     */
    protected function filterExecuteAttributes(array &$data, bool $removePk = false): void
    {
        $attrs = $this->getFillable();
        foreach ($data as $name => $val) {
            if (!in_array($name, $attrs)) {
                unset($data[$name]);
            }
        }
        if ($removePk && isset($data[$this->getKeyName()])) {
            unset($data[$this->getKeyName()]);
        }
    }

    public function filterEmptyAttributes(array &$data)
    {
        foreach ($data as $key => $datum) {
            if (!isset($datum) || $datum === 'undefined' || $datum === null){
                unset($data[$key]);
            }
        }
    }

    /**
     * 新增数据
     * @param array $data 需新增的数据
     * @return int
     */
    public function add(array $data): int
    {
        $this->filterExecuteAttributes($data, $this->incrementing);
        $model = $this->create($data);
        return $model->{$model->getKeyName()};
    }



    /**
     * 更新一条数据
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateById(int $id, array $data)
    {
        $this->filterExecuteAttributes($data, true);
        if (!$this->find($id)){
            throw new NormalStatusException("数据不存在或已被删除。");
        }
        $this->filterEmptyAttributes($data);
        return $this->find($id)->update($data) > 0;
    }

    /**
     * 单个或批量从回收站恢复数据
     * @param array $ids
     * @return bool
     */
    public function recovery(array $ids): bool
    {
        $this->withTrashed()->whereIn($this->getKeyName(), $ids)->restore();
        return true;
    }

    /**
     * 按条件更新数据
     * @param array $condition
     * @param array $data
     * @return bool
     */
    public function updateByCondition(array $condition, array $data): bool
    {
        $this->filterExecuteAttributes($data, true);
        return $this->query()->where($condition)->update($data) > 0;
    }

    /**
     * 读取一条数据
     * @param int $id
     * @return ZbyModel|null
     */
    public function read(int $id): ? ZbyModel
    {
        $row = $this->find($id);
        if (!$row){
            return null;
        }

        if ($this->auto_by_user) {
            $items = $this->autoByUser([$row]);
            $row = $items[0];
        }
        return $row;
    }

    /**
     * 获取列表数据
     * @param array|null $params
     * @param bool $isScope
     * @return array
     */
    public function getList(?array $params, bool $isScope = true): array
    {
        return $this->listQuerySetting($params, $isScope)->get()->toArray();
    }

    /**
     * 单个或批量软删除数据
     * @param array $ids
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function deleteById(array $ids): bool
    {

        $this->destroy($ids);
//        $manager = ApplicationContext::getContainer()->get(Manager::class);
//        $manager->destroy($ids, $this);
        /*
        $manager->destroy($ids,$this);*/
        return true;
    }

    /**
     * 单个或批量真实删除数据
     * @param array $ids
     * @return bool
     */
    public function realDelete(array $ids): bool
    {
        foreach ($ids as $id) {
            $model = $this->withTrashed()->find($id);
            $model && $model->forceDelete();
        }
        return true;
    }

    /**
     * @param array|null $params
     * @param bool $isScope 是否开启数据权限
     * @param string $pageName
     * @return array
     */
    public function getPageList(?array $params, bool $isScope = false ,string $pageName = 'page'): array
    {
        $paginate = $this->listQuerySetting($params, $isScope)->paginate(
            $params['pageSize'] ?? self::PAGE_SIZE, ['*'], $pageName, $params[$pageName] ?? 1
        );
        return $this->setPaginate($paginate);
    }

    /**
     * 获取树列表
     * @param array|null $params
     * @param bool $isScope
     * @param string $id
     * @param string $parentField
     * @param string $children
     * @return array
     */
    public function getTreeList(
        ?array $params = null,
        bool $isScope = true,
        string $id = 'id',
        string $parentField = 'parent_id',
        string $children='children'
    ): array
    {
        $params['_tree'] = true;
        $params['_tree_pid'] = $parentField;
        $data = $this->listQuerySetting($params, $isScope)->get();
        $data = new ZbyCollection($data);
        return $data->toTree([],  0, $id, $parentField, $children);
    }


    /**
     * 获取前端选择树
     * @param array $data
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \RedisException
     */
    public function getSelectTree(array $data): array
    {
        $query = $this->select(['id', 'parent_id', 'id AS value', 'name AS label'])
            ->where('status', self::ENABLE)->orderBy('sort', 'desc');

        /*if ( ($data['scope'] ?? false) && ! user()->isSuperAdmin()) {
            $roleData = ApplicationContext::getContainer()->get(SystemRoleMapper::class)->getMenuIdsByRoleIds(
                SystemUser::find(user()->getId(), ['id'])->roles()->pluck('id')->toArray()
            );

            $ids = [];
            foreach ($roleData as $val) {
                foreach ($val['menus'] as $menu) {
                    $ids[] = $menu['id'];
                }
            }
            unset($roleData);
            $query->whereIn('id', array_unique($ids));
        }*/

        if (!empty($data['onlyMenu'])) {
            $query->where('type', MenuModel::MENUS_LIST);
        }

        return $query->get()->toTree();
    }

    public function setPaginate(LengthAwarePaginatorInterface $paginator): array
    {
        $items = method_exists($this, 'handlePageItems') ? $this->handlePageItems($paginator->items()) : $paginator->items();
        $this->auto_by_user && $items = $this->autoByUser($items);
        return [
            'items' => $items,
            'page' => [
                'total' => $paginator->total(),
                'currentPage' => $paginator->currentPage(),
                'totalPage' => $paginator->lastPage()
            ]
        ];
    }

    /**
     * @param array|null $params
     * @param bool $isScope 是否开启数据权限
     * @return void
     */
    public function listQuerySetting(?array $params, bool $isScope): Builder
    {
        $query = (($params['recycle'] ?? false) === true) ? $this->onlyTrashed() : $this->query();

        if ($params['select'] ?? false) {
            $query = $query->select($this->filterQueryAttributes($params['select']) );
        }

        $query = $this->handleWith($query, $params);

        $query = $this->handleOrder($query, $params);

        $isScope && $query->userDataScope();

        return $this->handleSearch($query, $params);
    }

    public function handleWith(Builder $query, array $params): Builder
    {
        return $query;
    }

    public function handleSearch(Builder $query, array $params): Builder
    {
        return $query;
    }

    /**
     * 排序处理器
     * @param Builder $query
     * @param array|null $params
     * @return Builder
     */
    public function handleOrder(Builder $query, ?array &$params = null): Builder
    {
        // 对树型数据强行加个排序
        if (isset($params['_tree'])) {
            $query->orderBy($params['_tree_pid']);
        }

        if ($params['orderBy'] ?? false) {
            if (is_array($params['orderBy'])) {
                foreach ($params['orderBy'] as $key => $order) {
                    $query->orderBy($order, $params['orderType'][$key] ?? 'asc');
                }
            } else {
                $query->orderBy($params['orderBy'], $params['orderType'] ?? 'asc');
            }
        }

        return $query;
    }

    /**
     * 过滤查询字段不存在的属性
     * @param array $fields
     * @param bool $removePk
     * @return array
     */
    protected function filterQueryAttributes(array $fields, bool $removePk = false): array
    {
        $attrs = $this->getFillable();
        foreach ($fields as $key => $field) {
            if (!in_array(trim($field), $attrs) && mb_strpos(str_replace('AS', 'as', $field), 'as') === false) {
                unset($fields[$key]);
            } else {
                $fields[$key] = trim($field);
            }
        }
        if ($removePk && in_array($this->getKeyName(), $fields)) {
            unset($fields[array_search($this->getKeyName(), $fields)]);
        }
        return ( count($fields) < 1 ) ? ['*'] : $fields;
    }


    /**
     * 单个或批量启用数据
     * @param array $ids
     * @param string $fields
     * @return bool
     */
    public function enable(array $ids, string $field = 'status'): bool
    {
        $funcName = $field.'List';
        if (method_exists($this, $funcName)) {
            $result = $this->$funcName();
            return $this->whereIn($this->getKeyName(), $ids)->update([$field => $result['enable'] ]);
        }else{
            return $this->whereIn($this->getKeyName(), $ids)->update([$field => self::ENABLE]);
        }
    }

    /**
     * 单个或批量禁用数据
     * @param array $ids
     * @param string $field
     * @return bool
     */
    public function disable(array $ids, string $fields = 'status'): bool
    {
        $funcName = $fields.'List';
        if (method_exists($this, $funcName)) {
            $result = $this->$funcName();
            return $this->whereIn($this->getKeyName(), $ids)->update([$fields => $result['disable'] ]);
        }else{
            return $this->whereIn($this->getKeyName(), $ids)->update([$fields => self::DISABLE]);
        }
    }

    /**
     * 获取当前表的所有字段列表
     * @return array
     */
    public function getColumns(): array
    {
        $results = [];

        $resolver = ApplicationContext::getContainer()->get(ConnectionResolverInterface::class);
        $connection = $resolver->connection();

        $tablePrefix = env('DB_PREFIX');

        $columns = Schema::getColumnListing($this->table);

        foreach ($columns as $column) {
            $results[$column]['column_type'] = Schema::getColumnType($this->table, $column);
            $results[$column]['column_comment'] = $connection->getDoctrineColumn($tablePrefix.$this->table, $column)->getComment();
        }

        return $results;
    }


    /**
     * 自动装载调用用户信息
     * @param array $ids
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function autoByUser(array $items = [])
    {

        $this->autoField($this->created_column, $this->created_field, $items);
        $this->autoField($this->updated_column, $this->updated_field, $items);
        $this->autoField($this->deleted_column, $this->deleted_field, $items);
        return $items;
    }


    private function autoField(string $column_name, string $field_name, array &$items)
    {
        $itemCollection = Collection::make($items);

        $userIds = $itemCollection->pluck($column_name)->unique()->toArray();

        $userInterface = ApplicationContext::getContainer()->get(ZbyUserInterface::class);
        $results = $userInterface->getUserList($userIds);
        $resultCollection = Collection::make($results);

        $itemCollection->map(function ($item) use ($resultCollection, $column_name, $field_name){
            $item[$field_name] = $resultCollection->get($item[$column_name]);
        })->toArray();
    }




}