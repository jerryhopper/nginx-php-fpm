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
        error_log("Middleware");

        $uri = $request->getUri();

        $proto = $uri->getScheme();

        if( $_SERVER['HTTPS']=="On"){
            error_log("_SERVER['HTTPS']==\"On\"");
            $proto = "https";
        }else{
            error_log("_SERVER['HTTPS']==".$_SERVER['HTTPS']);
        }

        if( ! empty($request->getHeader("X-Forwarded-Proto") ) ){
            $proto = $request->getHeader("X-Forwarded-Proto");
            error_log("X-Forwarded-Proto not empty! (".$request->getHeader("X-Forwarded-Proto").")");
            error_log(json_encode( $request->getHeader("X-Forwarded-Proto") )) ;

        }else{
            error_log("X-Forwarded-Proto empty ");
        }

        if( ! empty($request->getHeader("X-Forwarded-Scheme") ) ){
            $proto =$request->getHeader("X-Forwarded-Scheme");
            error_log("X-Forwarded-Scheme not empty! (".$request->getHeader("X-Forwarded-Scheme").")");
            error_log(json_encode( $request->getHeader("X-Forwarded-Scheme") )) ;
        }else{
            error_log("X-Forwarded-Scheme empty");
        }



        error_log("proto: ".$proto);
        error_log("get: ".$uri->getHost());

        if ($uri->getHost() !== 'localhost' && $proto !== 'https') {
            $url = (string)$uri->withScheme('https')->withPort(443);

            $response = $this->responseFactory->createResponse();
            error_log("redirect");
            // Redirect
            $response = $response->withStatus(302)->withHeader('Location', $url);

            return $response;
        }

        return $handler->handle($request);
    }
}
