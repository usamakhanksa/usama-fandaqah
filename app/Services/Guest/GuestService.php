<?php

namespace App\Services\Guest;

use App\Models\Guest;
use Illuminate\Support\Facades\Http;

class GuestService
{
    public static function create(array $data): Guest
    {
        $data['team_id'] = currentTeam()->id;
        
        return Guest::create($data);
    }

    public static function update(Guest $guest, array $data): Guest
    {
        $guest->update($data);
        
        return $guest->refresh();
    }

    public static function verifyShomoos(Guest $guest, string $idNumber): bool
    {
        // Simulate Shomoos API verification
        // In production, this would call the actual Shomoos API
        try {
            $response = Http::timeout(10)
                ->post(config('services.shomoos.endpoint'), [
                    'id_number' => $idNumber,
                    'name' => $guest->name,
                ]);
                
            if ($response->successful()) {
                $data = $response->json();
                
                // Update guest with verified data
                $guest->update([
                    'id_number' => $idNumber,
                    'id_type' => $data['id_type'] ?? 'national_id',
                    'id_expire_date' => $data['expire_date'] ?? null,
                ]);
                
                return true;
            }
        } catch (\Exception $e) {
            // Log error
            \Log::error('Shomoos verification failed', [
                'guest_id' => $guest->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return false;
    }
}
