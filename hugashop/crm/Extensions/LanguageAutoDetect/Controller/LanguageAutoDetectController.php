<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Extensions\LanguageAutoDetect\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use App\Controller\BaseFrontController;
use HugaShop\Models\Localization\Language;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\LanguageAutoDetect\LanguageAutoDetect;

final class LanguageAutoDetectController extends BaseFrontController
{
    use BaseExtensionTrait;

    #[Route('/LanguageAutoDetect/switcher', name: 'ExtLanguageAutoDetectSwitcher', priority: 20)]
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

        // If translation file exists
        $translate_file_path = self::getExtensionDir() . 'translations/messages.' . $current->code . '.yaml';
        if (file_exists($translate_file_path)) {
            Design::$Translator->addResource('yaml', $translate_file_path, $current->code);
        }

        Design::assign('match_language', $match);
        Design::assign('current_language', $current);
        Design::assign('storage_key', LanguageAutoDetect::$storage_key);

        return $this->fetchExtResponse('modal.tpl');
    }
}
