<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Extensions\RedirectUrl\Models;

use HugaShop\Extensions\BaseExtensionModel;

final class RedirectUrl extends BaseExtensionModel
{
    protected static $table_fields = [
        'id'            => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'url'           => ['type' => 'varchar', 'req' => true],
        'redirect'      => ['type' => 'varchar', 'req' => true],
        'enabled'       => ['type' => 'tinyint', 'def' => 1],
        'comment'       => ['type' => 'varchar'],
        'transitions'   => ['type' => 'int',     'def' => 0],
    ];
}
