<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteContext;
use Symfony\Component\HttpFoundation\Session\Session;
use JerryHopper\OAuth2\Client\Provider\FusionAuth;

final class OauthCallbackAction
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




        if( ! array_key_exists('code',$request->getQueryParams())){
            ///oauth2/callback
            //$url = $routeParser->urlFor('login');

            return $response->withStatus(302)->withHeader('Location', $this->fusionAuth->getAuthorizationUrl() );
        }

        # check if url-param has code &&
        #if($request->getQueryParams()['code'] && $request->getQueryParams()['userState'] ){

        $user = null;
        try{
            // Try to get an access token (using the authorization code grant)
            $token = $this->fusionAuth->getAccessToken('authorization_code', [
                'code' => $request->getQueryParams()['code']
            ]);

        }catch(Exception $e){
            $user = null;
        }


        #error_log($token->jsonSerialize() );
        // Optional: Now you have a token you can look up a users profile data
        try {
            // We got an access token, let's now get the user's details
            $user = $this->fusionAuth->getResourceOwner($token);
            // Use these details to create a new profile
            //error_log(json_encode($user->toArray()));

        } catch (Exception $e) {

            // Failed to get user details
            $user = null;
        }

        // Use this to interact with an API on the users behalf
        #error_log( $token->getToken() );

        $data['userinfo']=$user->toArray();
        $data['tokeninfo']=$token->getValues();
        $data['token']=$token->getToken();
        $data['expires']=$token->getExpires();


        //error_log( "expires:".$token->getExpires());
        //error_log( "token:".$token->getToken());
        //error_log( "expired:".$token->hasExpired());
        //error_log( "getrefreshtoken:".json_encode($token->getRefreshToken() ) );


        //error_log( json_encode($token->getValues() ) );
        //error_log( json_encode($user->toArray() ) );
        //$user->toArray()['sub']


        // Pseudo example
        // Check user credentials. You may use the database here.

        #if($username === 'admin' && $password === 'secret') {
        #    $user = 1;
        #}

        #$user = null;

        $code       = $request->getQueryParams()['code'] ?? '';
        $locale     = $request->getQueryParams()['locale'] ?? '';
        $userstate  = $request->getQueryParams()['userState'] ?? '';















        #if($username === 'admin' && $password === 'secret') {
        #    $user = 1;
        #}

        // Clear all flash messages
        $flash = $this->session->getFlashBag();
        $flash->clear();

        // Get RouteParser from request to generate the urls
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        if ($user) {
            // Login successfully
            if ( $request->getQueryParams()['userState'] == "AuthenticatedNotRegistered" ){


            }else{

            }

            // Clears all session data and regenerates session ID
            $this->session->invalidate();
            $this->session->start();
            $this->session->set('authenticated', true);
            $this->session->set('user', $data);
            $flash->set('success', 'Login successfully');

            // Redirect to protected page
            $url = $routeParser->urlFor('dashboard');
        } else {
            $flash->set('error', 'Login failed!');

            // Redirect back to the login page
            $url = $routeParser->urlFor('login');
        }

        return $response->withStatus(302)->withHeader('Location', $url);
    }
}
