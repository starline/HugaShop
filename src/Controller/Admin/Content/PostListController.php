<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Admin\Content;

use HugaShop\Services\Design;
use HugaShop\Models\Request;
use App\Services\PaginationService;
use HugaShop\Models\Content\ContentPost;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostListController extends BaseAdminController
{
    #[Route('/admin/posts', name: 'PostListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('blog');

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable': {
                            ContentPost::updatePost($ids, ['visible' => 0]);
                            break;
                        }
                    case 'enable': {
                            ContentPost::updatePost($ids, ['visible' => 1]);
                            break;
                        }
                    case 'delete': {
                            foreach ($ids as $id) {
                                ContentPost::deletePost($id);
                            }
                            break;
                        }
                }
            }
        }

        $filter = PaginationService::initFilter();

        // Поиск
        $keyword = Request::get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        $posts_count =  ContentPost::countPosts($filter);
        $posts =        ContentPost::getPosts($filter);

        Design::assign('pagination', PaginationService::getPagination($posts_count, $filter));
        Design::assign('posts_count', $posts_count);
        Design::assign('posts', $posts);

        return $this->fetchResponse('content/post_list.tpl');
    }
}
