<?php

namespace App\Middleware;

use Aura\Accept\AcceptFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpException;

final class HttpExceptionMiddleware implements MiddlewareInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    private function negotiate ( $request){
        // assume the request indicates these Accept values (XML is best, then CSV,
        // then anything else)

        #$request->getHeader("Accept");
        #$_SERVER['HTTP_ACCEPT'] = 'application/xml;q=1.0,text/csv;q=0.5,*;q=0.1';

        // create the accept factory
        $accept_factory = new AcceptFactory( array('HTTP_ACCEPT'=>$request->getHeader("Accept")) );

        // create the accept object
        $accept = $accept_factory->newInstance();

        // assume our application has `application/json` and `text/csv` available
        // as media types, in order of highest-to-lowest preference for delivery
        $available = array(
            'application/json',
            'text/html',
        );

        // get the best match between what the request finds acceptable and what we
        // have available; the result in this case is 'text/csv'
        $media = $accept->negotiateMedia($available);
        return $media->getValue();
    }



    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (HttpException $httpException) {
            // Handle the http exception here
            $statusCode = $httpException->getCode();
            $response = $this->responseFactory->createResponse()->withStatus($statusCode);


            //
            $errorMessage = sprintf('%s %s', $statusCode, $response->getReasonPhrase());


            $contentType = $this->negotiate($request);

            // Json Error response.
            if($contentType!="text/html"){
                $response->getBody()->write(json_encode(array("code"=>$statusCode,"error"=>$response->getReasonPhrase())) );
                return $response->withHeader("Content-Type",$contentType);
            }

            // unauthorized, redirect to login
            if($statusCode==401){
                return $response->withHeader("Content-Type",$contentType)->withStatus(301)->withHeader("Location","/login");
            }





            error_log("!!");

            error_log($errorMessage);
            error_log($this->negotiate($request));
            error_log("!!");

            //print_r($request->getHeaders());
            // Log the errror message
            // $this->logger->error($errorMessage);

            // Render twig template or just add the content to the body
            //$response->getBody()->write($errorMessage);

            return $response->withHeader("Content-Type",$this->negotiate($request));
        }
    }
}
