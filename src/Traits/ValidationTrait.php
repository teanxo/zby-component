<?php

namespace Hyperf\Zby\Traits;

trait ValidationTrait
{

    protected array $rules = [];

    protected array $messages = [];

    protected array $groups = [];

    protected array $notGroups = [];


    // 验证是否为空
    protected function Required(\ReflectionProperty $property, $propertyAnnotation)
    {
        $this->setValidatorRule($property->getName(), 'required');
        $this->setValidatorMessage($property->getName(), 'required', $propertyAnnotation->message);
        if (!empty($propertyAnnotation->group)){
            $this->afterGroupHandler($propertyAnnotation->group, $property->getName(),'required');
        }else{
            $this->notGroups[$property->getName()][] = 'required';
            $this->notGroups[$property->getName()] = array_unique($this->notGroups[$property->getName()]);
        }
    }


    // 验证最大长度
    protected function Max(\ReflectionProperty $property, $propertyAnnotation)
    {
        $this->setValidatorRule($property->getName(), "max:{$propertyAnnotation->length}");
        $this->setValidatorMessage($property->getName(), 'max', $propertyAnnotation->message);
        if (!empty($propertyAnnotation->group)){
            $this->afterGroupHandler($propertyAnnotation->group, $property->getName(),"max:{$propertyAnnotation->length}");
        }else{
            $this->notGroups[$property->getName()][] = 'required';
            $this->notGroups[$property->getName()] = array_unique($this->notGroups[$property->getName()]);
        }
    }


    // 验证邮箱
    protected function Email(\ReflectionProperty $property, $propertyAnnotation)
    {
        $this->setValidatorRule($property->getName(), "email");
        $this->setValidatorMessage($property->getName(), 'email', $propertyAnnotation->message);
        if (!empty($propertyAnnotation->group)){
            $this->afterGroupHandler($propertyAnnotation->group, $property->getName(),'email');
        }else{
            $this->notGroups[$property->getName()][] = 'required';
            $this->notGroups[$property->getName()] = array_unique($this->notGroups[$property->getName()]);
        }
    }


    // 验证手机号
    protected function Mobile(\ReflectionProperty $property, $propertyAnnotation)
    {
        if ($propertyAnnotation->country === 'CN'){
            $this->setValidatorRule($property->getName(), "mobile_cn");
            $this->setValidatorMessage($property->getName(), 'mobile_cn', $propertyAnnotation->message);
            if (!empty($propertyAnnotation->group)){
                $this->afterGroupHandler($propertyAnnotation->group,$property->getName(), 'mobile_cn');
            }else{
                $this->notGroups[$property->getName()][] = 'required';
                $this->notGroups[$property->getName()] = array_unique($this->notGroups[$property->getName()]);
            }
        }


    }

    // 验证身份证号
    protected function IdCard(\ReflectionProperty $property, $propertyAnnotation)
    {
        if ($propertyAnnotation->country === 'CN'){
            $this->setValidatorRule($property->getName(), "idcard_cn");
            $this->setValidatorMessage($property->getName(), 'idcard_cn', $propertyAnnotation->message);
            if (!empty($propertyAnnotation->group)){
                $this->afterGroupHandler($propertyAnnotation->group, $property->getName(),'idcard_cn');
            }else{
                $this->notGroups[$property->getName()][] = 'required';
                $this->notGroups[$property->getName()] = array_unique($this->notGroups[$property->getName()]);
            }
        }
    }

    public function Constant(\ReflectionProperty $property, $propertyAnnotation)
    {
        var_dump($propertyAnnotation);
        $clazz = new \ReflectionClass($propertyAnnotation);
    }

    protected function afterGroupHandler(string $group, string $propertyName ,string $rule)
    {
        $this->groups[$group][$propertyName][] = $rule;
        $this->groups[$group] = array_unique($this->groups[$group]);
    }

    protected function setValidatorRule(string $propertyName, string $rule)
    {
        if (isset($this->rules[$propertyName])){
            $rules = explode('|', $this->rules[$propertyName]);
            $rules[] = $rule;
            $this->rules[$propertyName] = implode('|', array_unique($rules));
        }else{
            $this->rules[$propertyName] = $rule;
        }
    }

    protected function setValidatorMessage(string $propertyName, string $rule ,string $message)
    {
        $this->messages["{$propertyName}.{$rule}"] = $message;
    }

}