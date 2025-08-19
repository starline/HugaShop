<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\TimerGetHelpOffer\Models;

use HugaShop\Extensions\BaseExtensionModel;

final class HelpOffer extends BaseExtensionModel
{
    public $timestamps = true;

    protected static $table_fields = [
        'id'         => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'name'       => ['type' => 'varchar', 'req' => true],
        'phone'      => ['type' => 'varchar', 'req' => true],
        'email'      => ['type' => 'varchar'],
        'user_agent' => ['type' => 'varchar', 'access' => false],
        'ip'         => ['type' => 'varchar', 'access' => false, 'length' => 20],
    ];
}
