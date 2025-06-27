<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace App\Controller\Admin\Content;

use HugaShop\Models\Design;
use HugaShop\Models\Product\Product;
use HugaShop\Models\Request;
use App\Services\PaginationService;
use HugaShop\Models\Content\ContentPost;
use HugaShop\Models\Content\ContentComment;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommentListController extends BaseAdminController
{
    #[Route('/admin/comments', name: 'CommentListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('comment');

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (!empty($ids) && is_array($ids)) {
                switch (Request::post('action')) {
                    case 'approve': {
                            ContentComment::updateOne($ids, ['approved' => 1]);
                            break;
                        }
                    case 'delete': {
                            ContentComment::deleteOne($ids);
                            break;
                        }
                }
            }
        }

        $filter = PaginationService::initFilter();

        // Тип
        if ($type = Request::get('type', 'string')) {
            $filter['entity_type'] = ContentComment::getEntityClass($type);
            Design::assign('type', $type);
        }

        // Поиск
        if (!empty($keyword = Request::get('keyword', 'string'))) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        // Отображение
        $comments =         ContentComment::getComments($filter, 'entity');
        $comments_count =   ContentComment::getCommentsCount($filter);

        // Выбирает объекты, которые прокомментированы:
        $products_ids = [];
        $posts_ids = [];
        foreach ($comments as $comment) {
            if ($comment->type == 'product') {
                $products_ids[] = $comment->entity_id;
            }
            if ($comment->type == 'blog') {
                $posts_ids[] = $comment->entity_id;
            }
        }

        $products = Product::getProducts(['id' => $products_ids]);

        $posts = [];
        foreach (ContentPost::getPosts(['id' => $posts_ids]) as $p) {
            $posts[$p->id] = $p;
        }

        foreach ($comments as &$comment) {
            if ($comment->type == 'product' && isset($products[$comment->entity_id])) {
                $comment->product = $products[$comment->entity_id];
            }
            if ($comment->type == 'blog' && isset($posts[$comment->entity_id])) {
                $comment->post = $posts[$comment->entity_id];
            }
        }

        Design::assign('pagination', PaginationService::getPagination($comments_count, $filter));
        Design::assign('comments', $comments);
        Design::assign('comments_count', $comments_count);

        return $this->fetchResponse('content/comment_list.tpl');
    }
}
