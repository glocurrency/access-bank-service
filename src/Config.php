<?php

namespace GloCurrency\AccessBank;

use BrokeYourBike\AccessBank\Interfaces\ConfigInterface;

final class Config implements ConfigInterface
{
    private function getAppConfigValue(string $key): string
    {
        $value = \Illuminate\Support\Facades\Config::get("services.access_bank.api.$key");

        return is_string($value) ? $value : '';
    }

    public function getUrl(): string
    {
        return $this->getAppConfigValue('url');
    }

    public function getAuthUrl(): string
    {
        return $this->getAppConfigValue('auth_url');
    }

    public function getAppId(): string
    {
        return $this->getAppConfigValue('app_id');
    }

    public function getClientId(): string
    {
        return $this->getAppConfigValue('client_id');
    }

    public function getClientSecret(): string
    {
        return $this->getAppConfigValue('client_secret');
    }

    public function getResourceId(): string
    {
        return $this->getAppConfigValue('resource_id');
    }

    public function getSubscriptionKey(): string
    {
        return $this->getAppConfigValue('subscription_key');
    }
}
