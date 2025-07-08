<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 *
 */


namespace HugaShop\Extensions\InfoBlock\Models;

use HugaShop\Extensions\BaseExtensionModel;

final class InfoBlock extends BaseExtensionModel
{
    protected static $table_fields = [
        'id'       => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'name'     => ['type' => 'varchar', 'trans' => true, 'required' => 'true'],
        'body'     => ['type' => 'text',    'trans' => true],
        'position' => ['type' => 'int',     'def' => 0],
        'enabled'  => ['type' => 'tinyint', 'def' => 0],
    ];
}
