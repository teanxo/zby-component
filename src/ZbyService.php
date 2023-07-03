<?php

namespace Hyperf\Zby;

use Hyperf\Context\ApplicationContext;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Zby\Constants\CommonStatus;
use Psr\Http\Message\ResponseInterface;

abstract class ZbyService
{

    /**
     * @var ZbyModel
     */
    protected $model;

    public function __construct()
    {
        $this->assignModel();
    }

    abstract public function assignModel();


    /**
     * 读取一条数据
     * @param int $id
     * @return ZbyModel|null
     */
    public function read(int $id)
    {
        return $this->model->read($id);
    }

    /**
     * 新增数据
     * @param array $data
     * @return int
     */
    public function save(array $data): int
    {
        return $this->model->add($data);
    }

    /**
     * 更新一条数据
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        return $this->model->updateById($id, $data);
    }

    /**
     * 按照条件更新数据
     * @param array $condition
     * @param array $data
     * @return bool
     */
    public function updateBycondition(array $condition, array $data)
    {
        return $this->model->updateByCondition($condition, $data);
    }

    /**
     * 获取列表数据
     * @param array|null $params
     * @param bool $isScope
     * @param string $pageName
     * @return array
     */
    public function getPageList(?array $params, bool $isScope = true, string $pageName = 'page')
    {
        return $this->model->getPageList($params, $isScope, $pageName);
    }


    /**
     * 获取回收站列表数据
     * @param array|null $params
     * @param bool $isScope
     * @return array
     */
    public function getPageListByRecycle(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = true;
        return $this->model->getPageList($params, $isScope);
    }

    public function getTreeList(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = false;
        return $this->model->getTreeList($params, $isScope);
    }

    /**
     * 从回收站获取树列表
     * @param array|null $params
     * @param bool $isScope
     * @return array
     */
    public function getTreeListByRecycle(?array $params = null, bool $isScope = true): array
    {
        if ($params['select'] ?? null) {
            $params['select'] = explode(',', $params['select']);
        }
        $params['recycle'] = true;
        return $this->model->getTreeList($params, $isScope);
    }

    /**
     * 删除一条数据
     * @param array $ids
     * @return bool
     */
    public function delete(array $ids)
    {
        return !empty($ids) && $this->model->deleteById($ids);
    }

    /**
     * 单个或批量真实删除数据
     * @param array $ids
     * @return bool
     */
    public function realDelete(array $ids): bool
    {
        return !empty($ids) && $this->model->realDelete($ids);
    }

    /**
     * 单个或批量从回收站恢复数据
     * @param array $ids
     * @return bool
     */
    public function recovery(array $ids): bool
    {
        return !empty($ids) && $this->model->recovery($ids);
    }

    /**
     * 修改数据状态
     * @param int $id
     * @param string $value
     * @param string $field
     * @return bool
     */
    public function changeStatus(int $id, string $value, string $field = 'status'): bool
    {
        // 获取当前的状态值
        $funcName = $field.'List';
        if (method_exists($this->model, $funcName)){
            $result = $this->model->$funcName();
            $enableVal = $result['enable'];

            return $value == $enableVal ? $this->model->enable([ $id ], $field) : $this->model->disable([ $id ], $field);
        }else{
            return $value == $this->model::ENABLE ? $this->model->enable([ $id ], $field) : $this->model->disable([ $id ], $field);

        }
    }

    public function export(array $params, ?string $dto, string $filename = null, \Closure $callbackData = null): ResponseInterface
    {
        if (empty($dto)) {
            return ApplicationContext::getContainer()->get(ZbyResponse::class)->error('导出未指定DTO');
        }

        if (empty($filename)) {
            $filename = $this->model->getTable();
        }

        return (new ZbyCollection())->export($dto, $filename, $this->model->getList($params), $callbackData);
    }

    public function import(string $dto, ?\Closure $closure = null)
    {
        return $this->model->import($dto, $closure);
    }

}