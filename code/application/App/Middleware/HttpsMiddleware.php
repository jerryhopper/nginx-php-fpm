<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware.
 */
final class HttpsMiddleware implements MiddlewareInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * The constructor.
     *
     * @param ResponseFactoryInterface $responseFactory The response factory
     */
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Invoke middleware.
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {


        //error_log("For ".json_encode( $request->getHeaders("X-Forwarded-For") ));
        //error_log("Proto ".json_encode($request->getHeaders("X-Forwarded-Proto") ));
        //error_log("Scheme ".json_encode($request->getHeaders("X-Forwarded-Scheme") ));


        # [X-Forwarded-For] => 163.158.92.255
        # [X-Forwarded-Proto] => https
        # [X-Forwarded-Scheme] => https
        $proto = $uri->getScheme();

        if( ! empty($request->getHeader("X-Forwarded-Proto") ) ){
            $proto = $request->getHeader("X-Forwarded-Proto");
        }

        if( ! empty($request->getHeader("X-Forwarded-Scheme") ) ){
            $proto =$request->getHeader("X-Forwarded-Scheme");
        }

        $uri = $request->getUri();


        if ($uri->getHost() !== 'localhost' && $proto !== 'https') {
            $url = (string)$uri->withScheme('https')->withPort(443);

            $response = $this->responseFactory->createResponse();

            // Redirect
            $response = $response->withStatus(302)->withHeader('Location', $url);

            return $response;
        }

        return $handler->handle($request);
    }
}
