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
            'https://www.googleapis.com/auth/drive.install'
        ]);
        $this->client->setClientId($configuration['clientId']);
        $this->client->setClientSecret($configuration['clientSecret']);
        $this->client->setRedirectUri($configuration['callbackUrl']);

        if ($token = $session->get('token')) {
            $this->client->setAccessToken($token);
        }
    }

    public function init($app)
    {
        $authUrl = $this->client->createAuthUrl();

        return $app->redirect($authUrl);
    }

    public function authenticate($authCode)
    {
        $accessToken = $this->client->authenticate($authCode);

        $this->session->set('token', $accessToken);

        $this->client->setAccessToken($accessToken);
    }

    public function getClient()
    {
        return $this->client;
    }
}