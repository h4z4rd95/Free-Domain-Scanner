<?php
/**
 * API Configuration
 * External service credentials and settings
 */
return [
    'namecheap' => [
        'api_key' => getenv('NAMECHEAP_API_KEY') ?: '',
        'username' => getenv('NAMECHEAP_USERNAME') ?: '',
        'client_ip' => getenv('NAMECHEAP_CLIENT_IP') ?: '',
        'sandbox' => true, // Set to false in production
        'api_url' => 'https://api.sandbox.namecheap.com/xml.response',
        'production_url' => 'https://api.namecheap.com/xml.response',
    ],
    
    'currency' => [
        'sources' => explode(',', getenv('CURRENCY_SOURCES') ?: 'bonbast,tgju'),
        'cache_duration' => 3600, // 1 hour in seconds
        'default_rate' => 50000, // Fallback rate if all sources fail
    ],
    
    'hetzner' => [
        'api_token' => getenv('HETZNER_API_TOKEN') ?: '',
        'api_url' => 'https://api.hetzner.cloud/v1',
    ],
    
    'aws' => [
        'access_key' => getenv('AWS_ACCESS_KEY') ?: '',
        'secret_key' => getenv('AWS_SECRET_KEY') ?: '',
        'region' => getenv('AWS_REGION') ?: 'eu-central-1',
    ],
];
