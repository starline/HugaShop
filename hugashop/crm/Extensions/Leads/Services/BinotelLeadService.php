<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 * Service for processing Binotel calls and managing leads.
 */

namespace HugaShop\Extensions\Leads\Services;

use HugaShop\Models\Lead;
use HugaShop\Models\LeadCall;
use HugaShop\Models\User\User;

class BinotelLeadService
{
    public function handleIncomingCall(array $payload): Lead
    {
        $phone = $payload['phone'] ?? null;
        if (!$phone) {
            throw new \InvalidArgumentException('Phone number required');
        }

        $lead = Lead::firstOrCreate(
            ['phone' => $phone],
            [
                'client_id' => $this->findClientId($phone),
                'status'    => 'new',
            ]
        );

        LeadCall::create([
            'lead_id' => $lead->id,
            'phone'   => $phone,
            'type'    => 'incoming',
            'payload' => json_encode($payload),
        ]);

        return $lead;
    }

    private function findClientId(string $phone): int
    {
        $client = User::where('phone', $phone)->first();
        return $client?->id ?? 0;
    }
}

