<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.7
 *
 */

namespace App\Controller;

use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Models\User\User;
use HugaShop\Services\Request;
use App\Event\DesignBeforeFetchEvent;
use Symfony\Component\Asset\Packages;
use HugaShop\Models\Localization\Language;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bridge\Twig\Extension\ImportMapRuntime;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{

    public function __construct(
        public LocaleSwitcher $LocaleSwitcher,
        public TranslatorInterface $Translator,
        public Packages $Packages,
        public RequestStack $requestStack,
        public UrlGeneratorInterface $UrlGenerator,
        public ?Profiler $Profiler
    ) {}


    /**
     * Setup Controller
     */
    public function setupController(?string $theme = null)
    {

        // Inject Symfony session
        Request::startSession($this->requestStack->getSession());

        // Theme
        Design::setTheme($theme);

        // Assets
        Design::setPackages($this->Packages);

        // Links
        Design::setModifierPlugin('linkLang', $this, 'generateUrlWithLocale');
        Design::setModifierPlugin('link', $this->UrlGenerator, 'generate');

        Design::assign('route', $this->requestStack->getCurrentRequest()->attributes->get('_route'));

        // Locale
        $this->LocaleSwitcher->setLocale(Language::getCurrent()->code);
        Design::setTranslator($this->Translator);

        // Show Profiler
        if (!is_null($this->Profiler) and !User::authUser('manager')) {
            $this->Profiler->disable();
        }
    }


    /**
     * Fetch ALL Content
     * @param string $content_tpl
     * @param ?string $block
     * @param ?string $template_dir
     */
    public function fetch(string $content_tpl, ?string $block = null, ?string $template_dir = null)
    {

        // Point for BeforeFetchEvent
        $this->setEvent(new DesignBeforeFetchEvent($content_tpl));
        Design::setFunctionPlugin('importmap', $this, 'getImportmap');

        // Ajax
        if (Request::isAjax()) {
            $block = $block ?? 'content';
            Design::assign('ajax_block', $block);
            $content = Design::fetch('extends:wrapper/ajax.tpl|' . $content_tpl, $template_dir);
        }

        // Normal Content
        else {
            $content = Design::fetch($content_tpl, $template_dir) . Helper::getCoreStats();
            Request::setCurrentPage();
        }

        return $content;
    }


    /**
     * Fetch Response
     * @param string $content_tpl
     */
    public function fetchResponse(string $content_tpl, ?string $block = null, ?string $template_dir = null)
    {
        // flash message
        foreach (['success', 'empty', 'error'] as $message_type) {
            if (!empty($service_message = Helper::getSessionMessage('message_' . $message_type))) {
                Design::append('service_messages_' . $message_type, $service_message);
            }
        }

        return new Response($this->fetch($content_tpl, $block, $template_dir));
    }


    /**
     * Add Lazy Service to container
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'event.dispatcher' => EventDispatcherInterface::class,
            'kernel' => KernelInterface::class
        ]);
    }


    /**
     * Set Event
     * @param $event
     */
    public function setEvent($event)
    {
        return $this->container->get('event.dispatcher')->dispatch($event);
    }


    /**
     * Get importmap html
     * @param array $params
     */
    public function getImportmap(array $params)
    {
        if (empty($params['point'])) {
            return;
        }
        return $this->container->get('twig')->getRuntime(ImportMapRuntime::class)->importmap($params['point']);
    }


    /**
     * Generate URL with locale prefix
     * @param string $route_name
     * @param array $params
     * @param string $reference_type
     * @return string
     */
    public function generateUrlWithLocale(string $route_name, array $params = [], string $reference_type = 'absolute_path'): string
    {
        if ($route_name === 'current') {
            $url = $this->requestStack->getCurrentRequest()->getRequestUri();
        } else {
            $url = $this->UrlGenerator->generate($route_name, $params, UrlGeneratorInterface::ABSOLUTE_PATH);
        }

        $language_code = $params['locale'] ?? Language::checkOrGetCode();
        if ($language_code && $language_code !== Language::getMain()->code) {
            $url = '/' . $language_code . ($url === '/' || $url === '' ? '' : $url);
        }

        if ($reference_type === 'absolute_url') {
            $url = Config::get('root_url') . $url;
        }

        return $url;
    }


    /**
     * Extend redirectToRoute Symfony
     */
    protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
    {
        $response = parent::redirectToRoute($route, $parameters, $status);
        if (Request::isAjax()) {
            $url = $this->generateUrlWithLocale($route, $parameters);
            $response->headers->set('X-Redirect', $url);
            $response->setStatusCode(200);
            $response->headers->remove('Location');
            $response->setContent(json_encode(['redirect' => $url]));
            $response->headers->set('Content-Type', 'application/json');
        }
        return $response;
    }


    /**
     * Extend Redirect Symfony
     */
    protected function redirect(string $url, int $status = 302): RedirectResponse
    {
        $response = parent::redirect($url, $status);
        if (Request::isAjax()) {
            $response->headers->set('X-Redirect', $url);
            $response->setStatusCode(200);
            $response->headers->remove('Location');
            $response->setContent(json_encode(['redirect' => $url]));
            $response->headers->set('Content-Type', 'application/json');
        }
        return $response;
    }
}
