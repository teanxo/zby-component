<?php

namespace Hyperf\Zby\Interfaces;

interface ZbyDictionaryInterface
{
    /**
     * 获取某一项的数据信息
     * @param string $dic_mark
     * @return array
     */
    public function getDictionaryByDicMark(string $dic_mark);
}