<?php

namespace App\Middleware;

use App\Preferences;
use DI\Container;
use ErrorException;
use JerryHopper\OAuth2\Client\Provider\FusionAuth;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Routing\RouteContext;
use Symfony\Component\HttpFoundation\Session\Session;

use JerryHopper\EasyJwt\Decode;
use JerryHopper\ServiceDiscovery\Discovery;



final class AuthorizationMiddleware implements MiddlewareInterface
{
    private $hasSession = false;
    private $hasAuthHeader = false;

    private $redirectRoute;
    private $authorizationHeader = "Authorization";
    private $tokenType = "Bearer";
    private $tokenResult = false;

    static $allow = "none";
    private $redirectAfterLogin='dashboard';
    private $redirectUnregistered='AuthenticatedNotRegistered';

    private $issuer = "idp.surfwijzer.nl";
    private $audience = "false";


    /**
     * @var Session
     */
    private $session;

//    public $allow;

    public function __construct(Container $container)
    {
        #error_log("AuthorizationMiddleware::construct");
        //Session $session
        $this->session = $container->get(Session::class);
        $this->preferences = $container->get(Preferences::class);
        $this->fusionauth = $container->get(FusionAuth::class);
        $this->responseFactory  = $container->get(ResponseFactoryInterface::class);


        //$this->preferences->getOauthRedirectUrl();

        $parsedUrl = parse_url( $this->preferences->getOauthRedirectUrl() );

        #error_log($parsedUrl['path']);
        $this->redirectRoute = $parsedUrl['path'];



    }



    private function OauthCallback($request){
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $routeParser = $routeContext->getRouteParser();

        error_log("AuthorizationMiddleware::process, OauthCallback()");

        // Exchange code for token.
        try{
            // Try to get an access token (using the authorization code grant)
            $token = $this->fusionauth->getAccessToken('authorization_code', [
                'code' => $request->getQueryParams()['code']
            ]);

        }catch(Exception $e){
            error_log("AuthorizationMiddleware::process, OauthCallback() : Missing code, redirect to login");
            $flash->set('error', 'No code, redirect to login.');
            return $this->responseFactory->createResponse()->withStatus(302)->withHeader('Location', $this->fusionauth->getAuthorizationUrl() );
        }

        // Clears all session data and regenerates session ID
        $this->session->invalidate();
        $this->session->start();



        //print_r( $token->getToken() );

        $this->session->set( 'token', $token->jsonSerialize() );

        #$code       = $request->getQueryParams()['code'] ?? '';
        $locale     = $request->getQueryParams()['locale'] ?? '';
        $userstate  = $request->getQueryParams()['userState'] ?? '';





        if ( $request->getQueryParams()['userState'] == "AuthenticatedNotRegistered" ){
            $url = $routeParser->urlFor($this->redirectUnregistered );
            # redirect to registration form.
            error_log("AuthorizationMiddleware::process, OauthCallback() : userState: AuthenticatedNotRegistered, redirect to registerform");

            return $this->responseFactory->createResponse()->withStatus(302)->withHeader('Location', $url );
        }



        # redirect to dashboard.
        $url = $routeParser->urlFor($this->redirectAfterLogin);
        error_log("AuthorizationMiddleware::process, OauthCallback() : userState: Authenticated, redirect to home");
        return $this->responseFactory->createResponse()->withStatus(302)->withHeader('Location', $url );
    }





    private function isAllowed(ServerRequestInterface $request){

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        // return NotFound for non existent route
        if (empty($route)) {
            throw new HttpNotFoundException($request);
        }

        #var_dump($route);

        #$name = $route->getName();
        #$groups = $route->getGroups();
        #$methods = $route->getMethods();


        $arguments = $route->getArguments();
        if( isset($arguments['allow']) ){
            error_log("AuthorizationMiddleware::process, (Argument) allow:".$arguments['allow']);
            $isAllowedArgument = $arguments['allow'];

            #die();
        }else{
            //return false;
            error_log("AuthorizationMiddleware::process, (Argument) allow not set");
            $isAllowedArgument = 'all';
            #die();
        }


        $headerTokenResult=array('valid'=>false,'error'=>'defaults');
        $sessionTokenResult=array('valid'=>false,'error'=>'defaults');


        // Check if Authorization Header exists.
        if( $request->hasHeader($this->authorizationHeader)){
            #error_log("AuthorizationMiddleware::process, has authorization HEADER!");
            $this->hasAuthHeader = true;

            # get token from header.
            $token  = $this->extractTokenFromHeader($request);

            $headerTokenResult = $this->verifyToken($token,"header");

        }



        // Check Session

        if( $this->session->has("token") ){
            #error_log("AuthorizationMiddleware::process, has 'token' SESSION!");
            $this->hasSession = true;

            # Get token from the session
            $getTokenObj = new AccessToken($this->session->get('token'));
            $token =  $getTokenObj->getToken();

            $sessionTokenResult = $this->verifyToken($token,"session");


        }



        ##################################################

        $isAllowed=false;


        #error_log("AuthorizationMiddleware::process, allow: ".$isAllowedArgument);

        if( $isAllowedArgument=="all"){
            $isAllowed=true;

            if($sessionTokenResult['valid']){
                $this->tokenResult = $sessionTokenResult;
            }
            if($headerTokenResult['valid']){
                $this->tokenResult = $headerTokenResult;
            }


        }else if( $isAllowedArgument=="token" && $this->hasAuthHeader && ! $this->hasSession ){
            $isAllowed=true;

            if(!$headerTokenResult['valid']){
                $isAllowed=false;
            }else{
                $this->tokenResult = $headerTokenResult;
                $this->tokenResult = $headerTokenResult;
            }

        }else if( $isAllowedArgument=="session" && ! $this->hasAuthHeader && $this->hasSession ){
            $isAllowed=true;

            if(!$sessionTokenResult['valid']){
                $isAllowed=false;
            }else{
                $this->tokenResult = $sessionTokenResult;
            }

        }else if( $isAllowedArgument=="both" && $this->hasAuthHeader || $isAllowedArgument=="both" && $this->hasSession ){
            $isAllowed=true;

            if( !$sessionTokenResult['valid'] && !$headerTokenResult['valid']  ){
                $isAllowed=false;

            }else if( !$sessionTokenResult['valid'] && $headerTokenResult['valid'] ){
                $isAllowed=true;
                $this->tokenResult = $headerTokenResult;

            }else if( $sessionTokenResult['valid'] && !$headerTokenResult['valid']){
                $isAllowed=true;
                $this->tokenResult = $sessionTokenResult;

            }else if( $sessionTokenResult['valid'] && $headerTokenResult['valid'] ){
                $isAllowed=true;
                $this->tokenResult = $sessionTokenResult;

            }

        }

        if(!$isAllowed){
            error_log("AuthorizationMiddleware::process, Access Denied (".$this->tokenResult['tokenFrom'].")");
        }else{
            error_log("AuthorizationMiddleware::process, Access Granted (".$this->tokenResult['tokenFrom'].")");
        }


        return $isAllowed;
    }


    private function extractTokenFromHeader($request){
        return trim( str_replace( $this->tokenType ,"",$request->getHeader($this->authorizationHeader)[0] ) );
    }


    private function verifyToken($token,$from){


        try{
            $jwtPayloadData = new \JerryHopper\EasyJwt\Decode($token, $this->preferences->getOauthDiscoveryurl(),$this->audience ,$this->issuer);

        }catch(InvalidClaimException $e){
            error_log($e->getMessage());
            return array( 'error'=>$e->getMessage(),'valid'=>false,'tokenFrom'=>$from,'rawToken'=>false);

        }catch(\Exception $e){
            error_log($e->getMessage());
            return array( 'error'=>$e->getMessage(),'valid'=>false,'tokenFrom'=>$from,'rawToken'=>false );
            //return false;
        }
        return array('valid'=>true,'payload'=>$jwtPayloadData,'tokenFrom'=>$from,'rawToken'=>$token);
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {

        error_log("AuthorizationMiddleware::process");

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $routeParser = $routeContext->getRouteParser();

        // get route   Slim\Routing\Route
        //        parse_url($url);


        // Oauth Callback detection.
        if($route->getPattern()==$this->redirectRoute){
            // Check if the requested url is the oauth redirect url.
            return $this->OauthCallback($request);
        }


        if ( ! $this->isAllowed($request) ){
            error_log("AuthorizationMiddleware::process, NOT ALLOWED - RAISE HTTP ERROR BASED ON CONTENTTYPE");
            throw new HttpUnauthorizedException($request);
        };

        if( $this->preferences->getLocalDevelopmentIp()!=false ){
            $request = $request->withAttribute('ip_address',$this->preferences->getLocalDevelopmentIp() );
        }


        #error_log("AuthorizationMiddleware::process, Added request->withAttribute('token')");
        $request = $request->withAttribute('token',$this->tokenResult);

        //var_dump($this->tokenResult);


        #if( $isAllowed == false ){
        #    error_log("DENIED");
        #    //throw new \Exception("Access denied",401);
        #}else{
        #    error_log("OK");
        #}



        // https://akrabat.com/route-specific-configuration-in-slim/


        #$user = $request->getAttribute('user'); // set in an earlier middleware


        // retrieve the configured permission value
        //$route = $request->getAttribute('route');
        #$permission = $route->getArgument('permission', 'canDoNothing');

        #if ($user->isAllowed($permission) === false) {
        #    return new Response(StatusCode::HTTP_FORBIDDEN);
        #}


        return $handler->handle($request);

    }



}
