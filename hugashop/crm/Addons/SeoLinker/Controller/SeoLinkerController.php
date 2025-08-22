<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 */

namespace HugaShop\Addons\SeoLinker\Controller;

use HugaShop\Services\Design;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\SeoLinker\Models\SeoLinkerLink;
use HugaShop\Addons\SeoLinker\Models\SeoLinker;

final class SeoLinkerController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/SeoLinker/page/{id}', requirements: ['id' => '\\d+'], name: 'AddonSeoLinkerPage', priority: 20)]
    public function page(int $id)
    {
        $this->checkAdminAccess('addon');

        $page = SeoLinker::getOne($id);
        if (empty($page)) {
            return $this->redirectToRoute('AddonSeoLinker');
        }

        $links = SeoLinkerLink::getList(['from_url' => $page->url]);

        $links_in = SeoLinkerLink::getList([
            'to_url' => $page->url,
            'type'   => 'internal',
        ]);

        foreach ($links_in as $ln) {
            $src = SeoLinker::getOne(['url' => $ln->from_url]);
            $ln->from_id = $src->id ?? null;
        }

        Design::assign('page',      $page);
        Design::assign('links',     $links);
        Design::assign('links_in',  $links_in);
        Design::assign('addon',     $this->getAddon());

        return $this->fetchAddonResponse('page.tpl');
    }
}
