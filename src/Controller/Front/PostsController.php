<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Front;

use HugaShop\Services\Design;
use App\Services\PaginationService;
use App\Controller\BaseFrontController;
use HugaShop\Models\Content\ContentPost;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostsController extends BaseFrontController
{

    #[Route('/blog', name: 'PostList', priority: 10)]
    public function postList(): Response
    {

        $filter = PaginationService::initFilter(per_page: 10);
        $filter['visible'] = 1; # Выбираем только видимые посты

        $posts =       ContentPost::getListTranslate($filter, order: ['date', 'desc']);
        $posts_count = ContentPost::getCount($filter);

        // Передаем в шаблон
        Design::assign('posts', $posts);
        Design::assign('pagination', PaginationService::getPagination($posts_count, $filter));
        Design::assign('canonical', $this->generateUrlWithLocale('PostList'));

        return $this->fetchResponse('posts.tpl');
    }
}
