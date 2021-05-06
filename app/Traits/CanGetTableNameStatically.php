<?php

namespace App\Traits;

trait CanGetTableNameStatically {

    public static function getTableName()
    {
        return (new static)->getTable();
    }

}
