<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Admin\Content;

use HugaShop\Models\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Request;
use HugaShop\Models\Content\ContentComment;
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
        if (!empty($comment = Request::getDataAcces(ContentComment::getFields()))) {

            $comment->date = Helper::dateConvert($comment->date . ' ' . Request::post('time', 'string'), 'Y-m-d H:i:s');
            Design::setFlashMessage('update', ContentComment::updateOne($comment->id, $comment));

            return $this->redirectToRoute('CommentAdmin', ['id' => $comment->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $comment = ContentComment::getOne($id, ['user', 'entity']);
            if (empty($comment->id)) {
                return $this->redirectToRoute('CommentsAdmin');
            }
        }

        Design::assign('comment', $comment);

        return $this->fetchResponse('content/comment.tpl');
    }
}
