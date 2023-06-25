<?php

namespace App\Services\Dots\Providers;

use App\Services\Http\HttpClient;
use Longman\TelegramBot\Request;

class DotsProvider extends HttpClient
{
    public function getURLParams(): array
    {
        return [
            'headers' => [
                'Api-Auth-Token' => config('services.dots.api_auth_token'),
                'Api-Token' => config('services.dots.api_token'),
                'Api-Account-Token' => config('services.dots.api_account_token'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'query' => [
                'v' => '2.0.0',
            ],
            'json' => true,
        ];
    }


    public function getCities(): array
    {
        return $this->get(config('services.dots.host') . '/api/v2/cities', $this->getURLParams()) ?: [];
    }

    public function getCity(string $cityId): array
    {
        return $this->get(config('services.dots.host') . '/api/v2/cities/'.$cityId, $this->getURLParams()) ?: [];
    }

    public function getCompanies(string $cityId): array
    {
        return $this->get(config('services.dots.host') . '/api/v2/cities/'.$cityId . '/companies', $this->getURLParams()) ?: [];
    }

    public function getOneCompany(string $companyId): array
    {
        return $this->get(config('services.dots.host') . '/api/v2/companies/' . $companyId, $this->getURLParams()) ?: [];
    }

    public function getMenuItems(string $companyId): array
    {
        return $this->get(config('services.dots.host') . '/api/v2/companies/'. $companyId . '/items-by-categories', $this->getURLParams()) ?: [];
    }

    public function makeOrder(array $data): array
    {
        $orderData['orderFields'] = $data;
        return $this->post(config('services.dots.host'). '/api/v2/orders', $orderData, $this->getURLParams());
    }
}
