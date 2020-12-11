<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteContext;
use Symfony\Component\HttpFoundation\Session\Session;
use JerryHopper\OAuth2\Client\Provider\FusionAuth;

final class OauthLoginAction
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session,FusionAuth $fusionAuth)
    {
        $this->session = $session;
        $this->fusionAuth = $fusionAuth;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        error_log($this->fusionAuth->getAuthorizationUrl());
        return $response->withStatus(302)->withHeader('Location', $this->fusionAuth->getAuthorizationUrl() );

    }
}
