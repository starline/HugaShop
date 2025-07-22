<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.6
 *
 */

namespace App\Controller\Admin\Content;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use HugaShop\Models\Content\ContentComment;
use App\Services\ImageService;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommentController extends BaseAdminController
{
    #[Route('/admin/comment/{id}', requirements: ['id' => '\d+'], name: 'CommentAdmin')]
    public function index(int $id): Response
    {

        $this->checkAdminAccess('comment');

        #### Update
        ###########
        if (!empty($comment = Request::getInputCheckEditAccess(ContentComment::class, $id))) {

            $comment->date = Helper::dateConvert($comment->date . ' ' . Request::post('time', 'string'), 'Y-m-d H:i:s');
            Design::setFlashMessage('update', ContentComment::updateOne($comment->id, $comment));

            ImageService::catchImages($comment->id, 'comment', 'images');

            return $this->redirectToRoute('CommentAdmin', ['id' => $comment->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $comment = ContentComment::getOne($id, ['user', 'entity', 'images']);

            if (empty($comment->id)) {
                return $this->redirectToRoute('CommentsAdmin');
            }
        }

        Design::assign('comment', $comment);

        return $this->fetchResponse('content/comment.tpl');
    }
}
