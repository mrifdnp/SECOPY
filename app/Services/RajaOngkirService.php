<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RajaOngkirService
{
    /**
     * Create a new class instance.
     */

    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.rajaongkir.base_url');
        $this->apiKey = config('services.rajaongkir.key');
        $this->origin_address = config('services.rajaongkir.origin_address');
    }

    public function calculateCost($destination, $weight, $couriers, $priceType = 'lowest')
    {

        $payload = [
            'origin'      => (int) $this->origin_address,
            'destination' => (int) $destination,
            'weight'      => (int) $weight,
            'courier'     => $couriers, 
            'price'       => $priceType,
        ];

        $response = Http::asForm()
            ->withHeaders([
                'key' => $this->apiKey,
            ])
            ->post("{$this->baseUrl}/calculate/domestic-cost", $payload);

        if ($response->failed()) {
            throw new \Exception('RajaOngkir API error: ' . $response->body());
        }

        return $response->json();
    }
}
