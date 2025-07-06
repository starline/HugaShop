<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace HugaShop\Extensions;

use HugaShop\Models\BaseModel;
use HugaShop\Services\Helper;

abstract class BaseExtensionModel extends BaseModel
{
    public function __construct(array $attributes = [])
    {

        // Auto DB table naming
        $this->table ?? $this->table = 'ext_' . Helper::camelToSnakeCase(class_basename(static::class));

        parent::__construct($attributes);
    }
}
