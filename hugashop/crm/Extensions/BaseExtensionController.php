<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 */

namespace HugaShop\Extensions;

use HugaShop\Services\Config;
use App\Controller\BaseAdminController;
use Symfony\Contracts\Service\Attribute\Required;

class BaseExtensionController extends BaseAdminController
{

    #[Required]
    public function initBaseExtension() {}


    /**
     * Get Extension directory
     */
    public function getExtensionDir()
    {
        return Config::get('extension_dir') . $this->getName() . '/';
    }


    /**
     * Get Extension name (Module)
     */
    public function getName()
    {
        return class_basename(static::class);
    }
}
