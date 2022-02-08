<?php

namespace GloCurrency\ZenithBank;

use BrokeYourBike\ZenithBank\Interfaces\ConfigInterface;

final class Config implements ConfigInterface
{
    private function getAppConfigValue(string $key): string
    {
        $value = \Illuminate\Support\Facades\Config::get("services.zenith_bank.api.$key");
        return is_string($value) ? $value : '';
    }

    public function getUrl(): string
    {
        return $this->getAppConfigValue('url');
    }

    public function getUsername(): string
    {
        return $this->getAppConfigValue('username');
    }

    public function getPassword(): string
    {
        return $this->getAppConfigValue('password');
    }
}
