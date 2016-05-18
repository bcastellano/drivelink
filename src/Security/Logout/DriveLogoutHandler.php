<?php

namespace DriveLink\Security\Logout;

use DriveLink\GoogleClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * Revoke google token at logout
 */
class DriveLogoutHandler implements LogoutHandlerInterface
{
    protected $client;

    public function __construct(GoogleClient $client)
    {
        $this->client = $client;
    }

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $this->client->getClient()->revokeToken();
    }
}

