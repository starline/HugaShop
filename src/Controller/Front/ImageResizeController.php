<?php

/**
 * Сначала проверяется, есть ли уже файл на сервере через Nginx
 *
 * @author Andi Huga
 * @version 3.0
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

        // Редиректить все разрешенные расширение на webp
        // Удалить 26.08.2026
        list($root_name, $ext, $width, $height, $set_watermark, $cut) = Image::getResizeParams($filename);
        if (Image::isAllowedFormat($ext) and $ext !== 'webp') {

            $flag = $set_watermark ? 'w' : '';
            $flag .= $cut ? 'c' : '';

            $image_url = Image::getImageURL($root_name, $width, $height, $flag, 'webp');
            return $this->redirect($image_url, Response::HTTP_MOVED_PERMANENTLY);
        }

        $resized_filename = Image::resize($filename);

        if ($resized_filename and is_readable($resized_filename)) {
            $response = new Response(file_get_contents($resized_filename));
            $mime_type = mime_content_type($resized_filename) ?: 'image';
            $response->headers->set('Content-type', $mime_type);
            return $response;
        }

        return new Response('File not found', Response::HTTP_NOT_FOUND);
    }
}
