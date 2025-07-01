<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 */

namespace App\Services;

use HugaShop\Models\Image;
use HugaShop\Services\Request;

class ImageService
{

    /**
     * Catch image from POST
     */
    public static function catchImages(int $entity_id, string $entity_name, string $post_name = 'images')
    {

        $images         = Request::post($post_name, 'array');
        $current_images = Image::getImages($entity_id, $entity_name);

        // Удаление основных изображений
        foreach ($current_images as $image) {
            if (!in_array($image->id, $images)) {
                Image::deleteImage($image->id);
            }
        }

        // Порядок основных изображений
        $images_visible = Request::post($post_name . '_visible', 'array');
        foreach ($images as $position => $im_id) {
            $visible = isset($images_visible[$im_id]) ? intval($images_visible[$im_id]) : 1;
            Image::updateOne($im_id, ['position' => $position, 'visible' => $visible]);
        }

        // Загрузка основных изображений из интернета и drag-n-drop файлов
        if ($urls = Request::post($post_name . '_urls', 'array')) {
            $urls_visible   = Request::post($post_name . '_urls_visible', 'array');
            $dropped_images = Request::files('dropped_' . $post_name);
            foreach ($urls as $index => $url) {

                // Загрузка с компьютера
                if ($dropped_images) {
                    $key = array_search($url, $dropped_images['name'], true);
                    if ($key !== false) {

                        // Move and Resize
                        $image_name = Image::uploadImage($dropped_images['tmp_name'][$key], $dropped_images['name'][$key]);
                        if ($image_name) {
                            $new_id = Image::addImage($entity_id, $entity_name, (string) $image_name);
                            if ($new_id) {
                                $visible = isset($urls_visible[$index]) ? intval($urls_visible[$index]) : 1;
                                Image::updateOne($new_id, ['visible' => $visible]);
                            }
                        }
                    }
                }
            }
        }
    }
}
