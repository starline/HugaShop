<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.6
 *
 * Этот класс использует шаблоны posts.tpl и post.tpl
 *
 */

namespace App\Controller\Front;

use HugaShop\Models\Image;
use HugaShop\Models\Design;
use HugaShop\Models\Request;
use App\Services\PaginationService;
use App\Controller\BaseFrontController;
use HugaShop\Models\Content\ContentPost;
use HugaShop\Models\User\UserPermission;
use HugaShop\Models\Content\ContentComment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostController extends BaseFrontController
{

    #[Route('/blog', name: 'PostList', priority: 1)]
    public function postList(): Response
    {


        $filter = PaginationService::initFilter(20);
        $filter['visible'] = 1; # Выбираем только видимые посты

        $posts =        ContentPost::getPosts($filter); # Выбираем статьи из базы
        $posts_count =  ContentPost::countPosts($filter); # Вычисляем количество страниц

        // Передаем в шаблон
        Design::assign('posts', $posts);
        Design::assign('posts_count', $posts_count);
        Design::assign('pagination', PaginationService::getPagination($posts_count, $filter));
        Design::assign('canonical', $this->generateUrl('PostList'));

        return $this->fetchResponse('posts.tpl');
    }


    #[Route('/blog/{url}', name: 'Post')]
    public function blogPost(string $url): Response
    {

        $post = ContentPost::getPost($url);

        // Check if availiable
        if (empty($post) || (empty($post->visible) && empty(UserPermission::checkAccess('blog')))) {
            throw $this->createNotFoundException('Post does not found'); # 404
        }

        // Images
        $images = Image::getImages($post->id, 'post');
        $post->images = $images;
        $post->image = reset($images);

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
