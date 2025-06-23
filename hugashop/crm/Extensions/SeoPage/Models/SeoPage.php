<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 *
 */

namespace HugaShop\Extensions\SeoPage\Models;

use HugaShop\Extensions\BaseExtensionModel;

final class SeoPage extends BaseExtensionModel
{

    protected static $table_fields = [
        'id' =>                     ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>                   ['type' => 'varchar',       'required' => 'true'],
        'url' =>                    ['type' => 'varchar',       'required' => 'true'],
        'h1' =>                     ['type' => 'varchar'],
        'meta_title' =>             ['type' => 'varchar'],
        'meta_description' =>       ['type' => 'varchar'],
        'body' =>                   ['type' => 'text'],
        'position' =>               ['type' => 'int',           'def' => 0],
        'enabled' =>                ['type' => 'tinyint',       'def' => 0]
    ];
}
