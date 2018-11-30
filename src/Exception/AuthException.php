<?php

declare(strict_types=1);

namespace Realconnex\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class AuthException
 * @package App\Exception
 */
class AuthException extends \Exception
{
    /**
     * @var array Headers of exception
     */
    public $headers = [];

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers ?? [];
    }

    /**
     * AuthException constructor.
     *
     * @param string $text
     * @param bool $isInvalidToken
     */
    public function __construct(string $text = "You must be authorized to perform this action", bool $isInvalidToken = true)
    {
        parent::__construct($text, Response::HTTP_UNAUTHORIZED);

        if ($isInvalidToken) {
            $this->headers['WWW-Authenticate'] = "Bearer error=\"invalid_token\", error_description=\"The signature is invalid\"";
        }
    }
}
