<?php

namespace DriveLink\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Token for coke native users
 */
class DriveUserToken extends AbstractToken
{
    protected $accessToken;
    protected $code;

    /**
     * Constructor.
     *
     * @param string $code
     * @param array  $roles
     */
    public function __construct($code, array $roles = array())
    {
        if (empty($code)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->code = $code;

        parent::__construct($roles);

        if ($roles) {
            $this->setAuthenticated(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return $this->accessToken;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setAccessToken(\stdClass $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function serialize()
    {
        return serialize(array($this->accessToken, $this->code, parent::serialize()));
    }

    public function unserialize($str)
    {
        list($this->accessToken, $this->code, $parentStr) = unserialize($str);

        parent::unserialize($parentStr);
    }
}
