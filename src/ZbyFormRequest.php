<?php

declare(strict_types=1);


namespace Hyperf\Zby;

use Hyperf\Codec\Json;
use Hyperf\Validation\Request\FormRequest;


/**
 * FormRequest
 * 验证公共继承类
 */
class ZbyFormRequest extends FormRequest
{

    protected array $dataPool = [];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 公共规则
     * @return array
     */
    public function commonRules(): array
    {
        return [];
    }

    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        $operation = $this->getOperation();
        $method = $operation . 'Rules';
        $rules = ( $operation && method_exists($this, $method) ) ? $this->$method() : [];
        return array_merge($rules, $this->commonRules());
    }

    /**
     * @return string|null
     */
    protected function getOperation(): ?string
    {
        $path = explode('/', $this->path());
        do {
            $operation = array_pop($path);
        } while (is_numeric($operation));

        return $operation;
    }

    public function setTableName(string $tableName)
    {
        $this->dataPool['table_name'] = $tableName;
        return $this;
    }

    public function setCondition($fieldName, $condition = '=')
    {
        if (!isset($this->dataPool['conditions'])){
            $this->dataPool['conditions'] = [];
        }
        $this->dataPool['conditions'][] = ['field_name'=>$fieldName, 'condition'=>$condition];
        return $this;
    }

    public function getEncodeDataPool()
    {
        return Json::encode($this->dataPool);
    }
}