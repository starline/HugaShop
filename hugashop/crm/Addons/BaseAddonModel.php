<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 */

namespace HugaShop\Addons;

use HugaShop\Models\BaseModel;
use HugaShop\Services\Helper;

abstract class BaseAddonModel extends BaseModel
{
    public function __construct(array $attributes = [])
    {

        // Auto DB table naming
        $this->table ?? $this->table = 'addon_' . Helper::camelToSnakeCase(class_basename(static::class));

        parent::__construct($attributes);
    }
}
