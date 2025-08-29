<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 * 
 * @link https://openai.com/api/
 *
 */

namespace HugaShop\Addons\OpenAI\Services;

use OpenAI;
use HugaShop\Addons\BaseAddonTrait;

class OpenAIService
{

    use BaseAddonTrait;

    public static $models = [
        'gpt-4o'                => 'GPT-4o',        # $2.5      $8
        'gpt-4.1'               => 'GPT-4.1',       # $2        $8
        'gpt-5-mini'            => 'GPT-5 mini',    # $0.25     $2
        'gpt-5'                 => 'GPT-5'          # $1.5      $10
    ];

    /**
     * Create base chat
     */
    public static function chatCreate(string $system_content, string $user_content, string $model = 'gpt-4o')
    {

        if (empty($key = self::getSettings()?->api_key)) {
            return null;
        }

        $client = OpenAI::client($key);
        $result = $client->chat()->create([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system_content],
                ['role' => 'user', 'content' => $user_content],
            ],
        ]);

        return $result;
    }


    /**
     * Create base response
     */
    public static function responsesCreate(string $input, string $model = 'gpt-4o', float $temperature = 0.7)
    {

        if (empty($key = self::getSettings()?->api_key)) {
            return null;
        }

        $client = OpenAI::client($key);
        $result = $client->responses()->create([
            'model'         => $model,
            //'temperature'   => $temperature,
            'input'         => $input,
        ]);

        return $result;
    }
}
