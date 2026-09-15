<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

use Google\Client as GoogleClient;

function google_client(): GoogleClient {
    $client = new GoogleClient();
    $client->setClientId((string) envv('GOOGLE_CLIENT_ID'));
    $client->setClientSecret((string) envv('GOOGLE_CLIENT_SECRET'));
    $client->setRedirectUri((string) envv('GOOGLE_REDIRECT_URI'));
    $client->setScopes(['openid', 'email', 'profile']);
    $client->setAccessType('offline');
    $client->setPrompt('select_account');
    return $client;
}
