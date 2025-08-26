<?php

/**
 * Сначало проверяетя есть ли уже файл на сервере через Ngix
 *
 * @author Andi Huga
 * @version 2.9
 *
 */

namespace App\Controller\Front;

use HugaShop\Models\Image;
use HugaShop\Services\Helper;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\Cache;

/**
 * Use BaseController
 * We don't need BaseAdminController or BaseFrontController
 */
class ImageResizeController extends BaseController
{

    #[Route('/files/resize/{file}', name: 'ImageResize', priority: 10)]
    #[Cache(public: true, maxage: 315360000, mustRevalidate: true)]
    public function imageResize(string $file, Request $request): Response
    {
        $token = array_key_first($request->query->all());

        // Если нет входных данных
        if (empty($file) || empty($token)) {
            return new Response('File not found', Response::HTTP_NOT_FOUND);
        }

        $filename = urldecode($file);

        if (!Helper::checkToken($filename, $token, Image::$token_length)) {
            return new Response('File not found. Bad token', Response::HTTP_NOT_FOUND);
        }

        // TODO: редиректить все разрешенные расширение на webp

        $resized_filename = Image::resize($filename);

        if (!empty($resized_filename) and is_readable($resized_filename)) {
            $response = new Response(file_get_contents($resized_filename));
            $response->headers->set('Content-type', 'image/webp');
            return $response;
        }

        return new Response('File not found', Response::HTTP_NOT_FOUND);
    }
}
