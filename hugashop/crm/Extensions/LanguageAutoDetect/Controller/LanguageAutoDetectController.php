<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\LanguageAutoDetect\Controller;

use App\Controller\BaseFrontController;
use HugaShop\Extensions\BaseExtensionTrait;
use HugaShop\Extensions\LanguageAutoDetect\LanguageAutoDetect;
use HugaShop\Models\Localization\Language;
use HugaShop\Services\Design;
use HugaShop\Services\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LanguageAutoDetectController extends BaseFrontController
{
    use BaseExtensionTrait;

    #[Route('/LanguageAutoDetect/switcher', name: 'ExtLanguageAutoDetectSwitcher', priority: 20)]
    public function switcher(): Response
    {
        $matchCode = Request::get('match');
        $currentCode = Request::get('current');

        $languages = Language::getLanguages();
        $match = $languages->firstWhere('code', $matchCode);
        $current = $languages->firstWhere('code', $currentCode);

        if (empty($match) || empty($current)) {
            return new Response('', 404);
        }

        $translateFilePath = LanguageAutoDetect::getExtensionDir() . 'translations/messages.' . Design::$locale . '.yaml';
        if (file_exists($translateFilePath)) {
            Design::$Translator->addResource('yaml', $translateFilePath, Design::$locale);
        }

        Design::assign('match_language', $match);
        Design::assign('current_language', $current);

        return $this->fetchExtResponse('modal.tpl');
    }
}
