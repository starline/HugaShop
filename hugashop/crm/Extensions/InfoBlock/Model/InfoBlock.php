<?php

namespace HugaShop\Extensions\InfoBlock\Model;

use HugaShop\Extensions\BaseExtensionModel;

final class InfoBlock extends BaseExtensionModel
{
    protected static $table_fields = [
        'id'       => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'name'     => ['type' => 'varchar', 'required' => 'true'],
        'body'     => ['type' => 'text'],
        'position' => ['type' => 'int',     'def' => 0],
        'enabled'  => ['type' => 'tinyint', 'def' => 0],
    ];
}
