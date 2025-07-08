<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 */

namespace HugaShop\Extensions;

use App\Controller\BaseAdminController;
use Symfony\Contracts\Service\Attribute\Required;

class BaseExtensionController extends BaseAdminController
{

    #[Required]
    public function initBaseExtension()
    {

        dump('ext');
    }
}
