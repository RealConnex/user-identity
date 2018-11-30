<?php

declare(strict_types=1);

namespace Realconnex;

use Realconnex\Exception\AuthException;
use Realconnex\HttpRequest;
use Lcobucci\JWT\Parser;

/**
 * Class UserIdentity
 * @package Realconnex
 */
class UserIdentity
{
    const AUTH_SERVICE = 'mbau';

    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';
    const ROLE_SYSTEM = 'system';

    /**
     * @var int
     */
    public $userId;

    /**
     * @var array
     */
    public $userIdentity;

    /**
     * @var string
     */
    protected $authServiceName;

    /**
     * @var HttpRequest
     */
    protected $httpRequest;

    /**
     * UserIdentity constructor.
     *
     * @param HttpRequest $httpRequest
     * @param string|null $authServiceName
     * @throws AuthException
     */
    public function __construct(HttpRequest $httpRequest, ?string $authServiceName = null)
    {
        $this->httpRequest = $httpRequest;
        $this->authServiceName = $authServiceName ?? self::AUTH_SERVICE;

        $userId = null;
        $authToken = $this->httpRequest->getAuthToken();

        // Check if there was bearer provided in the header
        $authToken = trim(str_replace('bearer', '', $authToken));

        if (!empty($authToken)) {
            // Suppose that token already been validated on central bus
            $userId = $this->extractUserIdFromToken($authToken);
            if (empty($userId)) {
                throw new AuthException('Incorrect authorization data was provided');
            }
        }

        $this->setUserId($userId);
    }

    /**
     * Get user ID
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * Set user ID
     *
     * @param int|null $userId
     */
    private function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * Get auth service name
     *
     * @return string
     */
    public function getAuthServiceName(): string
    {
        return $this->authServiceName;
    }

    /**
     * Extract user ID, stored in JWT payload
     *
     * @param string $jwtString
     * @return int
     * @throws AuthException
     */
    public function extractUserIdFromToken(string $jwtString): int
    {
        try {
            $jwt = (new Parser())->parse($jwtString);
            $userId = $jwt->getClaim('userId');
        } catch (\Exception $exception) {
            throw new AuthException('Incorrect authorization data');
        }

        return $userId;
    }

    /**
     * Get user's identity
     */
    public function getUserIdentity(): ?array
    {
        if (empty($this->userIdentity)) {
            $userId = $this->getUserId();
            try {
                if (empty($userId)) {
                    return $this->userIdentity = $this->httpRequest
                        ->setProvideAuth(true)
                        ->sendRequest($this->getAuthServiceName(), 'api/v1/auth/system', HttpRequest::METHOD_GET);
                }

                $this->userIdentity = $this->httpRequest
                    ->setProvideAuth(true)
                    ->sendRequest($this->getAuthServiceName(), 'api/v1/users/' . $userId, HttpRequest::METHOD_GET);
                if (empty($this->userIdentity) || !is_array($this->userIdentity)) {
                    return null;
                }
            } catch (\Exception $exception) {
                return null;
            }
        }

        return $this->userIdentity;
    }
}
