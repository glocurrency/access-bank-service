<?php

namespace GloCurrency\AccessBank\Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use GloCurrency\AccessBank\Tests\TestCase;
use GloCurrency\AccessBank\Config;
use BrokeYourBike\AccessBank\Interfaces\ConfigInterface;

class ConfigTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_implemets_config_interface(): void
    {
        $this->assertInstanceOf(ConfigInterface::class, new Config());
    }

    /** @test */
    public function it_will_return_empty_string_if_value_not_found()
    {
        $configPrefix = 'services.access_bank.api';

        // config is empty
        config([$configPrefix => []]);

        $config = new Config();

        $this->assertSame('', $config->getUrl());
        $this->assertSame('', $config->getAuthUrl());
        $this->assertSame('', $config->getAppId());
        $this->assertSame('', $config->getClientId());
        $this->assertSame('', $config->getClientSecret());
        $this->assertSame('', $config->getResourceId());
        $this->assertSame('', $config->getSubscriptionKey());
    }

    /** @test */
    public function it_can_return_values()
    {
        $url = $this->faker->url;
        $authUrl = $this->faker->url;
        $appId = $this->faker->uuid;
        $clientId = $this->faker->uuid;
        $clientSecret = $this->faker->password();
        $resourceId = $this->faker->uuid;
        $subscriptionKey = $this->faker->password();

        $configPrefix = 'services.access_bank.api';

        config(["{$configPrefix}.url" => $url]);
        config(["{$configPrefix}.auth_url" => $authUrl]);
        config(["{$configPrefix}.app_id" => $appId]);
        config(["{$configPrefix}.client_id" => $clientId]);
        config(["{$configPrefix}.client_secret" => $clientSecret]);
        config(["{$configPrefix}.resource_id" => $resourceId]);
        config(["{$configPrefix}.subscription_key" => $subscriptionKey]);

        $config = new Config();

        $this->assertSame($url, $config->getUrl());
        $this->assertSame($authUrl, $config->getAuthUrl());
        $this->assertSame($appId, $config->getAppId());
        $this->assertSame($clientId, $config->getClientId());
        $this->assertSame($clientSecret, $config->getClientSecret());
        $this->assertSame($resourceId, $config->getResourceId());
        $this->assertSame($subscriptionKey, $config->getSubscriptionKey());
    }
}
