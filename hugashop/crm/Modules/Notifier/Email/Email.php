<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.5
 *
 */

namespace HugaShop\Modules\Notifier\Email;

use HugaShop\Api\Settings;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email as SEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class Email
{
    /**
     * Send Email
     *
     * @param string $message
     * @param array $params
     *
     * $params[key]:
     * from_email - required
     * to_email - required
     * from_name - required
     * subject - required
     * reply_to
     *
     */
    public function send(string $message, array $params)
    {

        // Defaul params
        if (empty($params['from_email'])) {
            $params['from_email'] = 'info@' . Settings::getParam('domain');
        }
        if (empty($params['from_name'])) {
            $params['from_name'] = Settings::getParam('company_name');
        }

        if (!empty($params['user']->email)) {
            $params['to_email'] = $params['user']->email;
        }

        $params_name = [
            'from_email',
            'to_email',
            'from_name',
            'subject',
            'reply_to'
        ];

        foreach ($params as $name => $value) {
            if (in_array($name, $params_name) and !empty($value)) {
                $$name = $value; # set var
            }
        }

        if (empty($to_email) || empty($from_email) || empty($message) || empty($subject)) {
            return false;
        }

        $transport = Transport::fromDsn('sendmail://default');
        $mailer = new Mailer($transport);
        $email = (new SEmail())
            ->from(new Address($from_email, $from_name))
            ->to($to_email)
            ->subject($subject)
            ->html($message);


        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            
            // логируй, уведомляй или используй fallback
            $logger->error('Mailer failed', ['error' => $e->getMessage()]);
        }
    }
}
