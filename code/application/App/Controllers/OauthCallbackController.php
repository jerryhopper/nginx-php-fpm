<?php


declare(strict_types=1);

namespace App\Controllers;

use App\Preferences;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Symfony\Component\HttpFoundation\Session\Session;
use JerryHopper\ServiceDiscovery\Discovery;
use League\OAuth2\Client\Provider\GenericProvider;
use JerryHopper\OAuth2\Client\Provider\FusionAuth;

class OauthCallbackController extends AbstractTwigController
{
    /**
     * @var Preferences
     */
    private $preferences;
    private $oauthclientProvider;


    /**
     * HomeController constructor.
     *
     * @param Twig        $twig
     * @param Preferences $preferences
     */
    public function __construct(Twig $twig, Preferences $preferences,Session $session, FusionAuth $OauthclientProvider )
    {
        // ,  $OauthclientProvider
        parent::__construct($twig);
        $this->session = $session;
        $this->preferences = $preferences;
        $this->oauthclientProvider = $OauthclientProvider;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $args = []): Response
    {
        $usr = $this->session->get('user');
        $usr = $this->session->get('authenticated');

        $code       = $request->getQueryParams()['code'] ?? '';
        $locale     = $request->getQueryParams()['locale'] ?? '';
        $userstate  = $request->getQueryParams()['userState'] ?? '';

        error_log("code:".$code);
        # idp.surfwijzer.nl/oauth2/token?



        // Try to get an access token using the authorization code grant.
        $accessToken = $this->oauthclientProvider->getAccessToken('authorization_code', [ 'code' => $code ]);

        // We have an access token, which we may use in authenticated
        // requests against the service provider's API.
    //    echo 'Access Token: ' . $accessToken->getToken() . "<br>";
    //    echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
    //    echo 'Expired in: ' . $accessToken->getExpires() . "<br>";
    //    echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br>";


        // Using the access token, we may look up details about the
        // resource owner.
        $resourceOwner = $this->oauthclientProvider->getResourceOwner($accessToken);

    //    var_export($resourceOwner->toArray());


 $data = array( "accesstoken"=>$accessToken->getToken(),'refresht'=>$accessToken->getRefreshToken() ,'exp'=>$accessToken->getExpires(),'resourceOwner'=>$resourceOwner->toArray());



        /*


                $data = (array)$request->getParsedBody();
                $username = (string)($data['username'] ?? '');
                $password = (string)($data['password'] ?? '');

                // Pseudo example
                // Check user credentials. You may use the database here.
                $user = null;
                if($username === 'admin' && $password === 'secret') {
                    $user = 1;
                }

                // Clear all flash messages
                $flash = $this->session->getFlashBag();
                $flash->clear();

                // Get RouteParser from request to generate the urls
                $routeParser = RouteContext::fromRequest($request)->getRouteParser();

                if ($user) {
                    // Login successfully
                    // Clears all session data and regenerates session ID
                    $this->session->invalidate();
                    $this->session->start();
                    $this->session->set('authenticated', true);
                    $this->session->set('user', $user);
                    $flash->set('success', 'Login successfully');

                    // Redirect to protected page
                    $url = $routeParser->urlFor('users-get');
                } else {
                    $flash->set('error', 'Login failed!');

                    // Redirect back to the login page
                    $url = $routeParser->urlFor('login');
                }

                return $response->withStatus(302)->withHeader('Location', $url);
                */

        return $this->render($response, 'callback.twig', [
            'pageTitle' => 'Home',
            'user' => $usr,
            'data' => $data,
            'rootPath' => $this->preferences->getRootPath(),
        ]);
    }
}
