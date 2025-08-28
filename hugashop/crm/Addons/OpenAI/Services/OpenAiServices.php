<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 */

namespace HugaShop\Addons\OpenAi\Services;

use OpenAI;

class OpenAiServices
{


    /**
     * Create base chat
     */
    public static function chatCreate(string $system_content, string $user_content, string $model = 'gpt-4o')
    {

        $key = self::getSettings()->api_key;
        if (empty($key)) {
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
}
