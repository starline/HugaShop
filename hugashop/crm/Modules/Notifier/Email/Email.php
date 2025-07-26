<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.8
 *
 */

namespace HugaShop\Modules\Notifier\Email;

use HugaShop\Models\Settings;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email as SEmail;
use HugaShop\Modules\Notifier\NotifierInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class Email implements NotifierInterface
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
    public static function send(string $message, array $params)
    {

        // Defaul params
        $params['from_email']   = $params['from_email'] ?? 'info@' . Settings::getParam('domain');
        $params['from_name']    = !empty($params['from_name']) ? $params['from_name'] : Settings::getParam('company_name');
        $params['to_email']     = $params['user']->email ?? $params['to_email'] ?? null;
        $params['subject']      = $params['subject'] ?? $params['from_name'];

        $params_name = [
            'from_email',
            'to_email',
            'from_name',
            'subject',
            'reply_to',
            'use_smpt',
            'host',
            'port',
            'username',
            'password'
        ];

        foreach ($params as $name => $value) {
            if (in_array($name, $params_name) and !empty($value)) {
                $$name = $value; # set var
            }
        }

        if (empty($to_email) || empty($from_email) || empty($message) || empty($subject)) {
            return false;
        }

        if ($use_smpt) {
            $dsn = sprintf('smtp://%s:%s@%s:%s', $username, $password, $host, $port);
        } else {
            $dsn = 'sendmail://default';
        }

        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);
        $email = (new SEmail())
            ->from(new Address($from_email, $from_name))
            ->to($to_email)
            ->subject($subject)
            ->html($message);

        if (!empty($reply_to)) {
            $email->replyTo($reply_to);
        }

        try {
            $mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {

            // TODO: логируй, уведомляй или используй fallback

        }
    }
}
