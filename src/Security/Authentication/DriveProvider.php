<?php

namespace DriveLink\Security\Authentication;

use DriveLink\GoogleClient;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 *
 */
class DriveProvider implements AuthenticationProviderInterface
{
    public function __construct(GoogleClient $googleClient, UserProviderInterface $userProvider)
    {
        $this->googleClient = $googleClient;
        $this->userProvider = $userProvider;
    }

    /**
     *
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        $accessToken = json_decode($this->googleClient->authenticate($token->getCode()));
        $userData = $this->googleClient->getClient()->verifyIdToken()->getAttributes();

        if (isset($userData['payload']['email'])) {
            $user = $this->userProvider->loadUserByUsername($userData['payload']['email']);
        }

        if (!$user instanceof UserInterface) {
            throw new BadCredentialsException('No user found for given credentials.');
        }

        $authenticatedToken = new DriveUserToken($token->getCode(), $user->getRoles());
        $authenticatedToken->setAccessToken($accessToken);
        $authenticatedToken->setAuthenticated(true);
        $authenticatedToken->setUser($user);

        return $authenticatedToken;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof DriveUserToken;
    }
}

