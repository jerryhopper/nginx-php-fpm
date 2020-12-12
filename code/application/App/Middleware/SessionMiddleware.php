<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

final class SessionMiddleware implements MiddlewareInterface
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $this->session->start();

        //error_log("For ".json_encode( $request->getHeaders("X-Forwarded-For") ));

        //error_log("Proto ".json_encode(empty($request->getHeader("X-Forwarded-Proto") ) ));
        //error_log("Scheme ".json_encode(empty($request->getHeader("X-Forwarded-Scheme") ) ));


        return $handler->handle($request);
    }
}
