<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace App\Controller\Admin\Content;

use HugaShop\Models\Image;
use HugaShop\Services\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Request;
use HugaShop\Models\SeoKeywords;
use App\Controller\BaseAdminController;
use HugaShop\Models\Content\ContentPost;
use HugaShop\Models\Localization\Language;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostController extends BaseAdminController
{
    #[Route('/admin/post', name: 'PostNewAdmin')]
    #[Route('/admin/post/{id}', requirements: ['id' => '\d+'], name: 'PostAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('blog');

        // Init content language
        Language::languageCatch();

        #### Update
        ###########
        if (!empty($post = Request::getDataAcces(ContentPost::getFields()))) {

            $post->date = Helper::dateConvert($post->date . ' 12:00', 'Y-m-d');

            if (empty($post->id)) {
                $post = Design::setFlashMessage('add', ContentPost::addPost($post));
            } else {
                Design::setFlashMessage('update', ContentPost::updatePost($post->id, $post));
            }

            SeoKeywords::catchKeywords($post->id, 'post');
            Image::catchImages($post->id, 'post', 'images');

            // Делаем редирект на страницу с ID
            return $this->redirectToRouteLang('PostAdmin', ['id' => $post->id]);
        }


        #### View
        #########
        if (!empty($id)) {

            $post = ContentPost::getPost(intval($id), join: ['images']);

            if (empty($post->id)) {
                return $this->redirectToRoute('PostListAdmin');
            }

            // SEO keywords
            $seo_keywords = SeoKeywords::getKeywords($post->id, 'post');

            Design::assign('seo_keywords', $seo_keywords);
            Design::assign('post', $post);
        }

        return $this->fetchResponse('content/post.tpl');
    }
}
