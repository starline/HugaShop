<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 */

namespace HugaShop\Models\Product;

use HugaShop\Models\BaseModel;

class BaseTranslationModel extends BaseModel
{

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'language_code' =>      ['type' => 'varchar']
    ];

    public function __construct(array $attributes = [])
    {
        // Fill out table fields

        parent::__construct($attributes);
    }
}
