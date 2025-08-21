<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\TimerGetHelpOffer\Models;

use HugaShop\Extensions\BaseExtensionModel;

final class HelpOfferStat extends BaseExtensionModel
{

    public $timestamps = true;
    protected static $table_fields = [
        'id'           => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'page'         => ['type' => 'varchar', 'req' => true],
        'submitted_at' => ['type' => 'datetime', 'null' => true],
    ];
}
