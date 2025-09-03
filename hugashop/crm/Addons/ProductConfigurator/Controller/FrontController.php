<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Addons\ProductConfigurator\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Controller\BaseFrontController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use HugaShop\Addons\ProductConfigurator\ProductConfigurator as ConfiguratorAddon;

final class FrontController extends BaseFrontController
{
    use BaseAddonTrait;

    #[Route('/configurator/{id}', name: 'AddonProductConfiguratorFront', priority: 1)]
    public function index(int $id): Response
    {
        $configurator = ConfiguratorAddon::get_configurator($id);
        if (empty($configurator)) {
            return $this->redirectToRoute('home');
        }

        if (Secure::checkCSRF()) {
            $selected = Request::post('option', []);
            $total = 0;
            $details = [];
            foreach ($configurator->steps as $step) {
                $opt_id = $selected[$step->id] ?? null;
                if ($opt_id) {
                    foreach ($step->options as $opt) {
                        if ($opt->id == $opt_id) {
                            $details[] = $step->name . ': ' . $opt->name;
                            $total += $opt->price;
                        }
                    }
                }
            }
            Design::assign('total', $total);
            Design::assign('details', $details);
            Design::assign('submitted', true);
            Design::assign('name', Request::post('name'));
            Design::assign('phone', Request::post('phone'));
            Design::assign('email', Request::post('email'));
        }

        Design::assign('configurator', $configurator);
        return $this->fetchAddonResponse('front.tpl');
    }
}
