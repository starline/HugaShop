<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.5
 *
 */

namespace HugaShop\Extensions\GoogleTag;

use HugaShop\Extensions\BaseExtension;

final class GoogleTag extends BaseExtension
{

    /**
     * Get Head block template
     */
    public function getFrontHeadTemplate()
    {
        if (!empty($this->ext_settings->enabled)) {
            return $this->fetchTemplate('templates/head_tag.tpl');
        }
        return;
    }


    /**
     * Get Body block template
     */
    public function getFrontBodyTemplate()
    {
        if (!empty($this->ext_settings->enabled)) {
            return $this->fetchTemplate('templates/body_tag.tpl');
        }
        return;
    }
}
