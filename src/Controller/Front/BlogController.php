<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 * Этот класс использует шаблоны blog.tpl и post.tpl
 *
 */

namespace App\Controller\Front;

use HugaShop\Api\Image;
use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Content\ContentPost;
use HugaShop\Api\Content\ContentComment;
use HugaShop\Api\User\UserPermission;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends BaseFrontController
{

    #[Route('/blog', name: 'Blog', priority: 1)]
    public function blogList(): Response
    {
        $items_per_page = 20; # Количество постов на 1 странице

        $filter = [];
        $filter['page'] = max(1, Request::get('page', 'int'));
        $filter['limit'] = $items_per_page;
        $filter['visible'] = 1; # Выбираем только видимые посты

        $posts_count =  ContentPost::countPosts($filter); # Вычисляем количество страниц
        $posts =        ContentPost::getPosts($filter); # Выбираем статьи из базы

        // Передаем в шаблон
        Design::assign('pages_count', ceil($posts_count / $items_per_page));
        Design::assign('current_page', $filter['page']);
        Design::assign('posts', $posts);
        Design::assign('canonical', $this->generateUrl('Blog'));

        return $this->fetchResponse('blog.tpl');
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
