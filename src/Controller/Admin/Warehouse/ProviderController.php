<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace App\Controller\Admin\Warehouse;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Models\Product\ProductProvider;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProviderController extends BaseAdminController
{

    #[Route('/admin/warehouse/provider', name: 'ProviderNewAdmin')]
    #[Route('/admin/warehouse/provider/{id}', requirements: ['id' => '\d+'], name: 'ProviderAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('warehouse_provider');


        #### Update
        ###########
        if (!empty($provider = Request::getInputCheckEditAccess(ProductProvider::class, $id))) {
            if (empty($provider->id)) {
                $provider = Design::setFlashMessage('add', ProductProvider::createOne($provider));
            } else {
                Design::setFlashMessage('update', ProductProvider::updateOne($provider->id, $provider));
            }

            return $this->redirectToRoute('ProviderAdmin', ['id' => $provider->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $provider = ProductProvider::getOne($id);
            if (empty($provider->id)) {
                return $this->redirectToRoute('ProviderListAdmin');
            }
        }

        Design::assign('provider', $provider);

        return $this->fetchResponse('warehouse/provider.tpl');
    }
}
