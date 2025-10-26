<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 */

namespace HugaShop\Addons\LanguageAutoDetect\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Addons\BaseAddonTrait;
use App\Controller\BaseFrontController;
use HugaShop\Services\TranslatorFactory;
use HugaShop\Models\Localization\Language;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\LanguageAutoDetect\LanguageAutoDetect;

final class LanguageAutoDetectController extends BaseFrontController
{
    use BaseAddonTrait;

    #[Route('/LanguageAutoDetect/switcher', name: 'AddonLanguageAutoDetectSwitcher', priority: 20)]
    public function switcher(): Response
    {
        $match_code     = Request::input('match');
        $current_code   = Request::input('current');

        $languages      = Language::getLanguages();
        $match          = $languages->firstWhere('code', $match_code);
        $current        = $languages->firstWhere('code', $current_code);

        if (empty($match) || empty($current)) {
            return new Response('', 404);
        }

        TranslatorFactory::addYamlResourse(self::getAddonDir() . 'translations/messages.' . $current->code . '.yaml', $current->code);

        Design::assign('match_language', $match);
        Design::assign('current_language', $current);
        Design::assign('storage_key', LanguageAutoDetect::$storage_key);

        return $this->fetchAddonResponse('modal.tpl');
    }
}
