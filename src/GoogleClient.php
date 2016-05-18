<?php

namespace DriveLink;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GoogleClient
{
    private $client;
    private $session;

    public function __construct(SessionInterface $session, array $configuration)
    {
        $this->session = $session;

        $this->client = new \Google_Client();
        $this->client->setApplicationName('DriveLink');
        $this->client->setScopes([
            \Google_Service_Drive::DRIVE_READONLY,
            'https://www.googleapis.com/auth/drive.install',
            'https://www.googleapis.com/auth/userinfo.email'
        ]);
        $this->client->setClientId($configuration['clientId']);
        $this->client->setClientSecret($configuration['clientSecret']);
        $this->client->setRedirectUri($configuration['callbackUrl']);

        if ($token = $session->get('_security_default')) {
            $token = unserialize($token);
            $this->client->setAccessToken(json_encode($token->getAccessToken()));
        }
    }

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function authenticate($authCode)
    {
        $accessToken = $this->client->authenticate($authCode);

        $this->client->setAccessToken($accessToken);

        return $accessToken;
    }

    public function getClient()
    {
        return $this->client;
    }
}