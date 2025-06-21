<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.4
 * 
 * Intervention Image
 * @link https://image.intervention.io/v3/getting-started/installation
 *
 */

namespace HugaShop\Models;

use HugaShop\Models\Helper;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

class Image extends BaseModel
{

    protected $table = 'content_image';

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar'],
        'entity_id' =>          ['type' => 'int'],
        'entity_name' =>        ['type' => 'varchar',       'lenght' => 25],
        'filename' =>           ['type' => 'varchar'],
        'position' =>           ['type' => 'created',       'def' => 0],
        'created' =>            ['type' => 'varchar',       'def' => 'CURRENT_TIMESTAMP']
    ];


    private static $allowed_extentions = ['png', 'gif', 'jpg', 'jpeg', 'ico', 'webp', 'bmp'];
    public static $token_lenght = 10;

    public function entity()
    {
        return $this->morphTo();
    }


    /**
     * Catch image from POST
     */
    public static function catchImages($entity_id, $entity_name, $post_name = 'images')
    {
        // Удаление основных изображений
        $images = Request::post($post_name, 'array');
        $current_images = Image::getImages($entity_id, $entity_name);
        foreach ($current_images as $image) {
            if (!in_array($image->id, $images)) {
                Image::deleteImage($image->id);
            }
        }

        // Порядок основных изображений
        $i = 0;
        foreach ($images as $im_id) {
            Image::updateOne($im_id, ['position' => $i]);
            $i++;
        }

        // Загрузка осноных изображений из интернета и drag-n-drop файлов
        if ($images = Request::post($post_name . '_urls')) {
            foreach ($images as $url) {

                // Если не пустой адрес и файл не локальный
                if (!empty($url) && $url != 'http://' && strstr($url, '/') !== false) {
                    Image::addImage($entity_id, $entity_name, $url);
                } elseif ($dropped_images = Request::files('dropped_' . $post_name)) {
                    $key = array_search($url, $dropped_images['name']);

                    // Ужимаем изображение до заданого размера
                    $image_name = Image::uploadImage($dropped_images['tmp_name'][$key], $dropped_images['name'][$key]);
                    if ($key !== false && $image_name) {
                        Image::addImage($entity_id, $entity_name, $image_name);
                    }
                }
            }
        }
    }


    /**
     * Get images
     * @param int|array $entity_id
     * @param string $entity_name - product
     */
    public static function getImages(int|array $entity_id, string $entity_name)
    {
        return self::getList(filter: ['entity_id' => $entity_id, 'entity_name' => $entity_name], order: 'position');
    }


    /**
     * Add image to DB. Check name
     * @param int $entity_id
     * @param string $entity_name
     * @param string $filename
     * @return int image_id
     */
    public static function addImage(int $entity_id, string $entity_name, string $filename)
    {

        // Check and Make uniq name
        $unique_name = $base_name = $filename;
        $i = 1;
        while (
            self::where('filename', $unique_name)
            ->exists()
        ) {
            $unique_name = $base_name . '-' . $i++;
        }

        // Создаем изображение
        $image = new self();
        $image->entity_id = $entity_id;
        $image->entity_name = $entity_name;
        $image->filename = $filename;
        $image->save();

        // Обновляем позицию = id
        $image->position = $image->id;
        $image->save();

        return $image->id;
    }


    /**
     * Delete Image
     * @param int $id
     */
    public static function deleteImage(int $id)
    {
        // Select file name
        $image = self::find($id);
        $filename = $image->filename;

        if (!$image) {
            return;
        }

        // Delete image by ID
        $image->delete();

        // Select images count by name
        $count = self::where('filename', $filename)->count();

        // If there is NOT image, delete file by name
        if ($count === 0) {
            $file = pathinfo($filename, PATHINFO_FILENAME);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            // Удалить все ресайзы
            $rezised_images = glob(Config::get('images_resized_dir') . $file . ".*x*." . $ext);
            if (!empty($rezised_images)) {
                foreach ($rezised_images as $f) {
                    @unlink($f);
                }
            }

            @unlink(Config::get('images_resized_dir') . $filename);
        }
    }


    /**
     * Making Image URL
     * @param $filename
     */
    public static function getURL($filename, $width = 0, $height = 0, $set_watermark = false)
    {
        $resized_filename = self::addResizeParams($filename, $width, $height, $set_watermark);
        $resized_filename_encoded = $resized_filename;

        if (substr($resized_filename_encoded, 0, 7) == 'http://' || substr($resized_filename_encoded, 0, 8) == 'https://') {
            $resized_filename_encoded = rawurlencode($resized_filename_encoded);
        }

        $resized_filename_encoded = rawurlencode($resized_filename_encoded);
        return Config::get('root_url') . '/' . Config::get('images_resized_url') . $resized_filename_encoded . '?' . Helper::makeToken($resized_filename, self::$token_lenght);
    }


    /**
     * Upload and add image to Database
     *
     * @param string $temp_filename
     * @param $name
     * @param $entity_id
     * @param $entity_name
     * @param $width
     * @param $height
     *
     * @return $id - image ID
     */
    public static function uploadAddImage(string $temp_filename, string $original_filename, int $entity_id, string $entity_name, ?int $width = null, ?int $height = null)
    {
        if ($image_name = self::uploadImage($temp_filename, $original_filename, $width, $height)) {
            if ($image_id = self::addImage($entity_id, $entity_name, $image_name)) {
                return $image_id;
            }
        }
        return false;
    }


    /**
     * Создание превью изображения
     *
     * @param $filename файл с изображением (без пути к файлу)
     * @param $max_w максимальная ширина
     * @param $max_h максимальная высота
     * @return string имя файла превью
     */
    public static function resize($filename)
    {

        list($source_file, $width, $height, $set_watermark) = self::getResizeParams($filename);

        // Если файл удаленный (http://|https://), зальем его себе
        if (substr($source_file, 0, 7) == 'http://' || substr($source_file, 0, 8) == 'https://') {

            // Имя оригинального файла
            if (!$original_file = self::downloadImage($source_file)) {
                return false;
            }
        } else {
            $original_file = $source_file;
        }

        $resized_file = self::addResizeParams($original_file, $width, $height, $set_watermark);

        // Пути к папкам с картинками
        $originals_dir = Config::get('images_originals_dir');
        $resized_dir = Config::get('images_resized_dir');

        // Создадим директорию, если ее нет
        if (!is_dir($resized_dir)) {
            mkdir($resized_dir, 0777, true);
        }

        // Проверяем что файл существует
        if (!is_file($originals_dir . $original_file)) {
            return false;
        }

        $watermark_offet_x = Settings::getParam('watermark_offset_x');
        $watermark_offet_y = Settings::getParam('watermark_offset_y');

        if ($set_watermark && is_file(Config::get('images_watermark_file'))) {
            $watermark = Config::get('images_watermark_file');
        } else {
            $watermark = null;
        }

        return self::resizeUploadImage($originals_dir . $original_file, $resized_dir . $resized_file, $width, $height, $watermark, $watermark_offet_x, $watermark_offet_y);
    }


    /**
     * Ресайз загруженых изображений
     *
     * @param string $original_file_path
     * @param string $new_file_path
     * @param $sharpen - четкость изображжения 0-100 (0 - без изменений)
     * @param $watermark_transparency - прозначность водяного знака 0-100 (больше - прозрачнее)
     * @param $quality - качество изображения
     */
    public static function resizeUploadImage(string $original_file_path, string $new_file_path, $width = null, $height = null, $watermark = null, $watermark_offet_x = 0, $watermark_offet_y = 0, $watermark_transparency = null, $sharpen = null, $quality = null)
    {

        // Настройки взять в config
        if (empty($width)) {
            $width = Config::get('images_max_size');
        }

        if (empty($height)) {
            $height = Config::get('images_max_size');
        }

        if (empty($quality)) {
            $quality = Config::get('images_quality');
        }

        // Четкость изображения.
        // Настройки сайта. 0-100 (больше - четче)
        if (empty($sharpen)) {
            $sharpen = min(100, (int)Settings::getParam('images_sharpen')) / 100;
        }

        // Прозрачность водяного знака. Настройки сайта
        if (empty($watermark_transparency)) {
            $watermark_transparency = min(100, (int)Settings::getParam('watermark_transparency'));
        }


        // create image manager with desired driver
        $manager = new ImageManager(Driver::class);

        $image = $manager->read($original_file_path); # read image from file system
        $image = $image->scaleDown(width: $width, height: $height); # resize image proportionally to width

        // insert watermark
        if (!empty($watermark)) {
            $water_image = $manager->read($watermark);
            $image_size = $image->size();

            // Делаем Watermark меньше на 10% от изображения
            $water_image = $water_image->scaleDown(width: $image_size->width() - $image_size->width() * 0.1, height: $image_size->height() - $image_size->height() * 0.1);
            $water_image_size = $water_image->size();

            $offset_x = min(($image_size->width() - $water_image_size->width()) * $watermark_offet_x / 100, $image_size->width());
            $offset_y = min(($image_size->height() - $water_image_size->height()) * $watermark_offet_y / 100, $image_size->height());

            $image = $image->place($water_image, 'top-left', $offset_x, $offset_y, $watermark_transparency);
        }

        if (!empty($sharpen)) {
            $image = $image->sharpen($sharpen);
        }

        $image->toWebp($quality)->save($new_file_path); # save modified image in new format 

        return $new_file_path;
    }


    /**
     * Добавляем параметры размера, водяного знака
     *
     * @param $filename
     * @param $width
     * @param $height
     * @param $set_watermark
     */
    public static function addResizeParams($filename, $width = 0, $height = 0, $set_watermark = false)
    {
        if ('.' != ($dirname = pathinfo($filename, PATHINFO_DIRNAME))) {
            $file = $dirname . '/' . pathinfo($filename, PATHINFO_FILENAME);
        } else {
            $file = pathinfo($filename, PATHINFO_FILENAME);
        }
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if ($width > 0 || $height > 0) {
            $resized_filename = $file . '.' . ($width > 0 ? $width : '') . 'x' . ($height > 0 ? $height : '') . ($set_watermark ? 'w' : '') . '.' . $ext;
        } else {
            $resized_filename = $file . '.' . ($set_watermark ? 'w.' : '') . $ext;
        }

        return $resized_filename;
    }


    /**
     * Разбирает параметры файла на парамеры
     * @param string $filename
     */
    public static function getResizeParams(string $filename)
    {

        // Определаяем параметры ресайза
        if (!preg_match('/(.+)\.([0-9]*)x([0-9]*)(w)?\.([^\.]+)$/', $filename, $matches)) {
            return false;
        }

        $file =             $matches[1];            # имя запрашиваемого файла
        $width =            $matches[2];            # ширина будущего изображения
        $height =           $matches[3];            # высота будущего изображения
        $set_watermark =    $matches[4] == 'w';        # ставить ли водяной знак
        $ext =              $matches[5];            # расширение файла

        return [$file . '.' . $ext, $width, $height, $set_watermark];
    }


    /**
     * Заливаем файл на сервер по http://
     * @param string $filename
     */
    public static function downloadImage(string $filename)
    {

        // Заливаем только если такой файла есть в базе
        $query = Database::placehold('SELECT 1 FROM __content_image WHERE filename=? LIMIT 1', $filename);
        if (!self::query($query)->result()) {
            return false;
        }

        // Имя оригинального файла
        $basename =         explode('&', pathinfo($filename, PATHINFO_BASENAME));
        $uploaded_file =    array_shift($basename);  # first
        $root_name =        urldecode(pathinfo($uploaded_file, PATHINFO_FILENAME));
        $ext =              pathinfo($uploaded_file, PATHINFO_EXTENSION);

        // Если такой файл существует, нужно придумать другое название
        $image_path = Config::get('images_originals_dir') . $root_name . '.' . $ext;
        while (file_exists($image_path)) {

            // Google likes '-'
            $image_path = Config::get('images_originals_dir') . $root_name . '-' . Helper::makeToken(uniqid(), 8) . '.' . $ext;
        }

        $new_name =  pathinfo($image_path, PATHINFO_BASENAME);

        // Перед долгим копированием займем это имя
        self::query('UPDATE __content_image SET filename=? WHERE filename=?', $new_name, $filename);

        fclose(fopen($image_path, 'w'));
        copy($filename, $image_path);

        return $new_name;
    }


    /**
     * Заливаем файл на сервер
     * Через эту функцию заливаем оригинальные изображения
     *
     * @param string $temp_filename
     * @param string $original_filename
     * @param $width
     * @param $height
     * @return $image_name
     */
    public static function uploadImage(string $temp_filename, string $original_filename, ?int $width = null, ?int $height = null)
    {

        $original_filename =    Helper::slugEn($original_filename); # Lowcase EN charcters
        $root_name =            substr(pathinfo($original_filename, PATHINFO_FILENAME), 0, 16); # Max 16 characters
        $ext =                  pathinfo($original_filename, PATHINFO_EXTENSION);

        $image_path = Config::get('images_originals_dir') . $root_name . '.' . $ext;

        // Пропускаем только разрешенные расширения
        if (in_array(strtolower($ext), self::$allowed_extentions)) {

            // Если файл с таким именем уже существует, добавим token
            while (file_exists($image_path)) {

                // Google likes '-'
                $image_path = Config::get('images_originals_dir') . $root_name . '-' . Helper::makeToken(uniqid(), 8) . '.' . $ext;
            }

            // Изменяем размер изображения
            if (!empty($image_path = self::resizeUploadImage($temp_filename, $image_path, $width, $height))) {
                return pathinfo($image_path, PATHINFO_BASENAME);
            }
        }

        return null;
    }


    /**
     * Сlear image resize folder
     * @param ?string $dir
     */
    public static function clearImageResize(?string $dir = null)
    {
        $dir = $dir ?: Config::get('images_resized_dir');

        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    @unlink($dir . "/" . $file);
                }
            }
            closedir($handle);
        }
    }
}
