<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace HugaShop\Extensions\SeoPage\Models;

use HugaShop\Extensions\BaseExtensionModel;

final class SeoPage extends BaseExtensionModel
{

    protected static $table_fields = [
        'id' =>                     ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>                   ['type' => 'varchar',       'trans' => true,  'required' => 'true'],
        'url' =>                    ['type' => 'varchar',       'required' => 'true'],
        'h1' =>                     ['type' => 'varchar',       'trans' => true],
        'meta_title' =>             ['type' => 'varchar',       'trans' => true],
        'meta_description' =>       ['type' => 'varchar',       'trans' => true],
        'body' =>                   ['type' => 'text',          'trans' => true],
        'position' =>               ['type' => 'int',           'def' => 0],
        'enabled' =>                ['type' => 'tinyint',       'def' => 0]
    ];
}
