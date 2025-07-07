<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.7
 *
 * Этот класс использует шаблоны posts.tpl и post.tpl
 *
 */

namespace App\Controller\Front;

use HugaShop\Services\Design;
use App\Controller\BaseFrontController;
use HugaShop\Models\Content\ContentPost;
use HugaShop\Models\User\UserPermission;
use HugaShop\Models\Content\ContentComment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostController extends BaseFrontController
{
    #[Route('/blog/{url}', name: 'Post')]
    public function post(string $url): Response
    {

        $post = ContentPost::getOneTranslate(['url' => $url], join: ['image']);

        // Check if availiable
        if (empty($post) || (empty($post->visible) && empty(UserPermission::checkAccess('blog')))) {
            throw $this->createNotFoundException('Post does not found'); # 404
        }

        // Comments
        ContentComment::handleComments($post->id, ContentPost::class);

        // Next and Preview post
        Design::assign('next_post', ContentPost::getNextPost($post->id));
        Design::assign('prev_post', ContentPost::getPrevPost($post->id));

        // Meta
        if (empty($post->meta_description)) {
            $post->meta_description = $post->meta_title;
        }

        Design::assign('post', $post);
        Design::assign('meta_title', $post->meta_title);
        Design::assign('meta_description', $post->meta_description);
        Design::assign('canonical', $this->generateUrl('Post', ['url' => $post->url]));

        return $this->fetchResponse('post.tpl');
    }
}
