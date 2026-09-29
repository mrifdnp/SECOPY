<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AgenWebsiteService
{
    protected $apiKey;
    protected $baseUrl;
    protected $originZipcode;

    public function __construct()
    {
        $this->apiKey = env('AGENWEBSITE_API_KEY');
        $this->baseUrl = 'https://api.agenwebsite.com/v1';
        $this->originZipcode = env('ORIGIN_POSTAL_CODE');
    }

    /**
     * Get J&T Cargo cost with caching and fallback
     */
    public function getJtcCost($destinationZipcode, $weight)
    {
        if (!$this->apiKey || !$this->originZipcode || !$destinationZipcode) {
            return null; // Missing config or destination zipcode, silently fail (fallback)
        }

        $cacheKey = "agenwebsite_jtc_{$this->originZipcode}_{$destinationZipcode}_{$weight}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($destinationZipcode, $weight) {
            try {
                $payload = [
                    'shipper' => [
                        'zipcode' => $this->originZipcode
                    ],
                    'destination' => [
                        'zipcode' => $destinationZipcode
                    ],
                    'weight' => (int) $weight,
                    'couriers' => ['jtc'],
                    'sort' => 'cheapest'
                ];

                Log::info('AgenWebsite Payload: ', $payload);

                $response = Http::withHeaders([
                    'x-api-key' => $this->apiKey,
                    'Content-Type' => 'application/json'
                ])->post("{$this->baseUrl}/rates", $payload);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['success']) && $data['success'] && isset($data['data']['rates'])) {
                        // Find JTC rate
                        $jtcRate = collect($data['data']['rates'])->firstWhere('courier_code', 'jtc');
                        
                        if ($jtcRate) {
                            return $this->formatToRajaOngkir($jtcRate);
                        }
                    }
                } else {
                    Log::warning('AgenWebsite API Error: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('AgenWebsite Exception: ' . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Format AgenWebsite response to match RajaOngkir schema
     */
    private function formatToRajaOngkir($rate)
    {
        $code = $rate['courier_code'] === 'jtc' ? 'JNT Cargo' : $rate['courier_code'];
        
        return [
            'name' => $rate['courier_name'],
            'code' => $code,
            'service' => $rate['service_name'] ?? 'J&T Cargo',
            'description' => $rate['service_name'] ?? 'J&T Cargo',
            'cost' => $rate['cost'],
            'etd' => str_replace(' days', '', $rate['etd_text'] ?? '') . ' day'
        ];
    }
}
