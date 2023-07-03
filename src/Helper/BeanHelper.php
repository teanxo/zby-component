<?php

namespace Hyperf\Zby\Helper;

class BeanHelper
{
    public static function toBean(array $array, string|object $clazz): object
    {
        $refectionClass = new \ReflectionClass($clazz);
        $instance = $refectionClass->newInstance();

        foreach ($array as $name => $value) {
            if ($refectionClass->hasProperty($name)){
                $property = $refectionClass->getProperty($name);
                $property->setAccessible(true);
                $property->setValue($instance, $value);
            }
        }
        return $instance;
    }

    /**
     * 将数组内的所有类属性转为数组
     */
    public static function toBeans(array $array, string|object $clazz): array
    {
        $result = [];
        foreach ($array as $item){
            $result[] = self::toBean($item, $clazz);
        }
        return $result;
    }

    /**
     * 将类对象转化为数组
     * @param object $clazz 类实例
     * @param bool $isSnake 是否将驼峰转为下划线
     * @param bool $isInitialized 是否校验未初始化的属性
     * @return array
     */
    public static function toArray(object $clazz, $isSnake = false): array
    {
        $result = [];
        $refectionClass = new \ReflectionClass($clazz);
        $properties = $refectionClass->getProperties();
        foreach ($properties as $property){
            $property->setAccessible(true);
            $value = null;
            // 判断property是否为未初始化的属性
            if ($property->isInitialized($clazz)){
                $value = $property->getValue($clazz);
            }
            $result[$isSnake ? Str::snake($property->getName()) : $property->getName()] = $value;
            $result[$property->getName()] = $value;
        }
        return $result;
    }
}