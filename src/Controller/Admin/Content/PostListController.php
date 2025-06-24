<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace App\Controller\Admin\Content;

use HugaShop\Models\Design;
use HugaShop\Models\Request;
use HugaShop\Models\Settings;
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

        $filter = [];
        $filter['page'] = max(1, Request::get('page', 'int'));
        $filter['limit'] = Request::get('page', 'string') == 'all' ? 'all' : Settings::getParam('products_num_admin');

        // Поиск
        $keyword = Request::get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        $posts_count =  ContentPost::countPosts($filter);
        $posts =        ContentPost::getPosts($filter);

        Design::assign('pages_count', ceil($posts_count / Settings::getParam('products_num_admin')));
        Design::assign('current_page', $filter['limit'] = 'all' ? 'all' : $filter['page']);
        Design::assign('posts_count', $posts_count);
        Design::assign('posts', $posts);

        return $this->fetchResponse('content/post_list.tpl');
    }
}
