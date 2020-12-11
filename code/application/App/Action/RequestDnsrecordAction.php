<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteContext;
use Symfony\Component\HttpFoundation\Session\Session;

final class RequestDnsrecordAction
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $data = (array)$request->getParsedBody();
        $ipadress = (string)($data['ipadress'] ?? '');

        // Pseudo example
        // Check user credentials. You may use the database here.



        // Get RouteParser from request to generate the urls
        //$routeParser = RouteContext::fromRequest($request)->getRouteParser();



        return $response->withStatus(302)->withHeader('Location', $url);
    }
}
