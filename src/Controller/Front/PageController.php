<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.9
 *
 * Этот класс использует шаблон page.tpl
 *
 */

namespace App\Controller\Front;

use HugaShop\Services\Design;
use HugaShop\Models\Content\ContentPage;
use HugaShop\Models\User\UserPermission;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends BaseFrontController
{

    #[Route('/info/{url}', name: 'Page')]
    public function page(string $url): Response
    {

        $page = ContentPage::getOneTranslate(['url' => $url]);

        if (empty($page) || (!UserPermission::checkAccess('page') and !$page->visible)) {
            throw $this->createNotFoundException('Page does not found'); # 404
        }

        Design::assign('page', $page);
        Design::assign('meta_title', $page->meta_title);
        Design::assign('meta_description', $page->meta_description);
        Design::assign('canonical', $this->generateUrlWithLocale('Page', ['url' => $page->url]));

        return $this->fetchResponse('page.tpl');
    }
}
