<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace HugaShop\Extensions\InfoBlock;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Models\Localization\Language;
use HugaShop\Extensions\BaseExtension;
use HugaShop\Extensions\InfoBlock\Models\InfoBlock as InfoBlockModel;

final class InfoBlock extends BaseExtension
{

    /**
     * Get block template
     * Use Cache
     */
    public static function getTemplate(array $params = [])
    {

        if (empty($params['id'])) {
            return;
        }

        $enabled = $params['enabled'] ?? '1';

        $lang = Language::getCurrent()->code;

        $cache_item = Cache::cache(InfoBlockModel::class)->getItem('item_' . $params['id'] . '_' . $lang);
        if (!$cache_item->isHit()) {

            $block = InfoBlockModel::getOneTranslate(['id' => intval($params['id']), 'enabled' => $enabled]);
            if (empty($block->body)) {
                return;
            }

            Design::assign('InfoBlock', $block->body);
            $info_block = self::fetchTemplate('templates/info_block.tpl');

            Cache::cache(InfoBlockModel::class)->save($cache_item->set($info_block));
        } else {
            $info_block = $cache_item->get();
        }

        return $info_block;
    }
}
