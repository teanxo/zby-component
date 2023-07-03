<?php

namespace Hyperf\Zby\Listener;


use Hyperf\Context\ApplicationContext;
use Hyperf\DbConnection\Db;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;
use Hyperf\Zby\Interfaces\ZbyDictionaryInterface;

#[Listener]
class ValidatorFactoryResolvedListener implements ListenerInterface
{


    protected $validatorFactory;

    public function listen(): array
    {
        return [
            ValidatorFactoryResolved::class
        ];
    }

    public function process(object $event): void
    {
        $this->validatorFactory = $event->validatorFactory;
        $this->mobileCN();
        $this->idCardCN();
        $this->enum();
        $this->dict();
    }

    // 手机号
    protected function mobileCN()
    {
        $this->validatorFactory->extend('mobile_cn', function ($attribute, $value, $parameters, $validator) {
            return preg_match("/^1[34578]\d{9}$/", $value) === 1;
        });
    }

    // 中国大陆-身份证号
    protected function idCardCN()
    {
        $this->validatorFactory->extend('idcard_cn', function ($attribute, $value, $parameters, $validator) {
            return preg_match("/^\d{6}(19|20)\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[\dXx]$/", $value) === 1;
        });
    }

    // 验证枚举 枚举类中存在的数据进行匹配，可支持多个枚举类
    protected function enum()
    {
        $this->validatorFactory->extend('enum', function ($attribute, $value, $parameters, $validator){
            if (is_array($parameters)){
                foreach ($parameters as $parameter){
                    $reflectionClass = new \ReflectionClass($parameter);
                    $constants = $reflectionClass->getConstants();

                    foreach ($constants as $key => $val) {
                        if ($val == $value){
                            return true;
                        }
                    }
                }
            }
            return false;
        });
    }

    public function dict()
    {
        $this->validatorFactory->extend('dict', function ($attribute, $value, $parameters, $validator){
            $dictionaryInterface = ApplicationContext::getContainer()->get(ZbyDictionaryInterface::class);
            $list = $dictionaryInterface->getDictionaryByDicMark($parameters[0]);
            var_dump(in_array($value,$list));
            if (empty($list) || !in_array($value,$list)){
                return false;
            }
            return true;
        });
    }

}