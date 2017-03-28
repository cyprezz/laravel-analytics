<?php

namespace Spatie\Analytics;

use Google_Client;
use Google_Service_Analytics;
use Illuminate\Contracts\Cache\Repository;

class AnalyticsClientFactory
{
    private static $config = [];

    public static function createForConfig(array $analyticsConfig)
    {
        if (!empty(static::$config)) {
            $analyticsConfig = static::$config;
        } else {
            static::$config = $analyticsConfig;
        }

        $authenticatedClient = self::createAuthenticatedGoogleClient($analyticsConfig);

        $googleService = new Google_Service_Analytics($authenticatedClient);

        return self::createAnalyticsClient($analyticsConfig, $googleService);
    }

    public static function createAuthenticatedGoogleClient(array $config)
    {
        $client = new Google_Client();

        $client->setClassConfig(
            'Google_Cache_File',
            'directory',
            $config['cache_location'] or storage_path('app/laravel-google-analytics/google-cache/')
        );

        $credentials = $client->loadServiceAccountJson(
            $config['service_account_credentials_json'],
            'https://www.googleapis.com/auth/analytics.readonly'
        );

        $client->setAssertionCredentials($credentials);

        return $client;
    }

    protected static function createAnalyticsClient(array $analyticsConfig, Google_Service_Analytics $googleService)
    {
        $client = new AnalyticsClient($googleService, app(Repository::class));

        $client->setCacheLifeTimeInMinutes($analyticsConfig['cache_lifetime_in_minutes']);

        return $client;
    }
}
