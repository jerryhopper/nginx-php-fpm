<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Slim\Routing\RouteContext;
use Symfony\Component\HttpFoundation\Session\Session;

#use Psr\Http\Message\ResponseFactory as Response;
#use Psr\Http\Message\ResponseInterface as Response;
#use Slim\Psr7\Response;

final class OauthMiddleware implements MiddlewareInterface
{
    /**
     * @var Session
     */
    private $session;
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(Session $session)
    {
        $this->session = $session;
        // ResponseFactoryInterface $responseFactory

        //$this->responseFactory = $responseFactory;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler

    ): ResponseInterface{

        # check if url-param has code &&
        #if($request->getQueryParams()['code'] && $request->getQueryParams()['userState'] ){

        #}


        #if(){

        #}
        //error_log(json_encode($this->session->all()));

        $u = $this->session->get("user");

        //error_log(json_encode($u) );

        if($u && $u['expires']<time()){
            // expired!
            // User is not logged in. Redirect to login page.
            error_log("Token expired!");

            $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
            $this->session->invalidate();
            $responseBody = $psr17Factory->createStream('unauthorized');

            $response = $psr17Factory->createResponse(401)->withBody($responseBody)->withHeader('Location', "/logout");

            return $response;

        }

        //error_log(json_encode($u['expires']) );
        //error_log(json_encode(time()) );

        return $handler->handle($request);
/*

        if ($this->session->get('user')) {
            // User is logged in
            return $handler->handle($request);
        }
        $code       = $request->getQueryParams()['code'] ?? '';
        $locale     = $request->getQueryParams()['locale'] ?? '';
        $userstate  = $request->getQueryParams()['userState'] ?? '';





        // User is not logged in. Redirect to login page.
        //$routeParser = RouteContext::fromRequest($request)->getRouteParser();
        //$url =  $routeParser->urlFor('login');




        #
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();

        $responseBody = $psr17Factory->createStream('unauthorized');
        $response = $psr17Factory->createResponse(401)->withBody($responseBody);//->withHeader('Location', $url);

        return $response;*/
    }
}
