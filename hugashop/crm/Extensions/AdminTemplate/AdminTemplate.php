<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 */

namespace HugaShop\Extensions\AdminTemplate;

use HugaShop\Extensions\BaseExtension;

final class AdminTemplate extends BaseExtension
{

    /**
     * For admin panel use default settings template
     */
    public function index()
    {
        return $this->getTemplatePath('template.tpl');
    }
}
