<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace HugaShop\Addons\SmsSender\Models;

use HugaShop\Addons\BaseAddonModel;

final class SmsSender extends BaseAddonModel
{

    protected static $table_fields = [
        'id' =>                         ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>                       ['type' => 'varchar',       'req' => true],
        'notifier_id' =>                ['type' => 'int'],
        'landing_url' =>                ['type' => 'varchar'],
        'template_id' =>                ['type' => 'int'],
        'count' =>                      ['type' => 'int',           'def' => 0, 'access' => false],
        'date' =>                       ['type' => 'datetime'],
        'user_list' =>                  ['type' => 'text'],
        'product_list' =>               ['type' => 'text'],
        'category_list' =>              ['type' => 'text'],
        'created' =>                    ['type' => 'datetime',      'def' => 'CURRENT_TIMESTAMP'],
    ];
}
