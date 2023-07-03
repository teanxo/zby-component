<?php

namespace Hyperf\Zby\Interfaces;

interface ZbyUserInterface
{
    public function getUserList(array $ids, $columns = ['id','nickname']);
}