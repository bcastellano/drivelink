<?php

namespace DriveLink\Security\Firewall;

use DriveLink\GoogleClient;
use DriveLink\Security\Authentication\DriveUserToken;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpFoundation\Request;

/**
 * Listener que comprueba si ha de usar nuestro Authentication provider para CokeId
 */
class DriveListener implements ListenerInterface
{
    protected $tokenStorage;
    protected $authManager;
    protected $googleClient;
    protected $httpUtils;
    protected $options;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authManager,
        GoogleClient $googleClient,
        HttpUtils $httpUtils,
        array $options
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authManager = $authManager;
        $this->googleClient = $googleClient;
        $this->httpUtils = $httpUtils;
        $this->options = $options;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->hasSession()) {
            throw new \RuntimeException('This authentication method requires a session.');
        }

        // check login path to go google auth
        if ($this->httpUtils->checkRequestPath($request, $this->options['login_path'])) {
            $event->setResponse($this->httpUtils->createRedirectResponse($request, $this->googleClient->getAuthUrl()));
        }

        // check callback url to login user in session
        if ($this->httpUtils->checkRequestPath($request, $this->options['callback_path'])) {
            $token = new DriveUserToken($request->query->get('code'), ['ROLE_USER']);

            try {
                $authToken = $this->authManager->authenticate($token);
                $this->tokenStorage->setToken($authToken);

                return;
            } catch (AuthenticationException $failed) {
                throw new AccessDeniedHttpException('Bad credentials: '.$failed->getMessage());
            }
        }
    }
}
