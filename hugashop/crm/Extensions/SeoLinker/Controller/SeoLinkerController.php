<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 */

namespace HugaShop\Extensions\SeoLinker\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\SeoLinker\Models\SeoLinkerLink;
use HugaShop\Extensions\SeoLinker\Models\SeoLinker as SeoLinkerModel;

final class SeoLinkerController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/SeoLinker/page/{id}', requirements: ['id' => '\\d+'], name: 'ExtSeoLinkerPage', priority: 20)]
    public function page(int $id)
    {
        $this->checkAdminAccess('extension');

        $page = SeoLinkerModel::getOne($id);
        if (empty($page)) {
            return $this->redirectToRoute('ExtSeoLinker');
        }

        $links = SeoLinkerLink::getList(['from_url' => $page->url]);

        $links_in = SeoLinkerLink::getList([
            'to_url' => $page->url,
            'type'   => 'internal',
        ]);

        foreach ($links_in as $ln) {
            $src = SeoLinkerModel::getOne(['url' => $ln->from_url]);
            $ln->from_id = $src->id ?? null;
        }

        Design::assign('page', $page);
        Design::assign('links', $links);
        Design::assign('links_in', $links_in);
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('page.tpl');
    }
}
