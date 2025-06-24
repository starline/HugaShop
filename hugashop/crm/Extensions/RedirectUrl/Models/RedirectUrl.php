<?php

namespace HugaShop\Extensions\RedirectUrl\Models;

use HugaShop\Extensions\BaseExtensionModel;

final class RedirectUrl extends BaseExtensionModel
{
    protected static $table_fields = [
        'id'            => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'url'           => ['type' => 'varchar', 'required' => true],
        'redirect'      => ['type' => 'varchar', 'required' => true],
        'enabled'       => ['type' => 'tinyint', 'def' => 1],
        'comment'       => ['type' => 'varchar'],
        'transitions'   => ['type' => 'int',     'def' => 0],
    ];
}
