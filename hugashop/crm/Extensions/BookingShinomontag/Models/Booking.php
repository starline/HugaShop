<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\BookingShinomontag\Models;

use HugaShop\Extensions\BaseExtensionModel;

final class Booking extends BaseExtensionModel
{
    public $timestamps = true;

    protected static $table_fields = [
        'id'      => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'date'    => ['type' => 'date',    'req' => true],
        'time'    => ['type' => 'time',    'req' => true],
        'name'    => ['type' => 'varchar', 'req' => true],
        'phone'   => ['type' => 'varchar', 'req' => true, 'length' => 32],
        'comment' => ['type' => 'text'],
    ];
}
