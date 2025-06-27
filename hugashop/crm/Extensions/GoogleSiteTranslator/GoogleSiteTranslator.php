<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 * 
 * @link https://github.com/get-web/google-translate-custom-widget
 *
 * Sometimes there may be some content on your page that you don't want to translate.
 * You can now add class="notranslate" to any HTML element to prevent that element from being translated.
 * For example, you may want to do something like:
 * Email us at <span class="notranslate">sales at mydomain dot com</span>
 *
 * FinishTranslate - The event is triggered when the site translation is finished
 * document.addEventListener("FinishTranslate", function (e) {
 *   //... some code
 * });
 *
 * All available languages and their ISO-639-1 code
 * @link https://cloud.google.com/translate/docs/languages
 *
 * Use Js Cookie
 * @link https://github.com/js-cookie/js-cookie
 *
 */

namespace HugaShop\Extensions\GoogleSiteTranslator;

use HugaShop\Extensions\BaseExtension;

final class GoogleSiteTranslator extends BaseExtension
{
    /**
     * Get block template
     */
    public function getFrontBodyTemplate()
    {
        if (!empty($this->settings->enabled)) {
            return $this->fetchTemplate('translator.tpl');
        }
        return;
    }
}
