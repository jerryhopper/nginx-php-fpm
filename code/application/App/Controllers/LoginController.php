<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Preferences;
use JerryHopper\OAuth2\Client\Provider\FusionAuth;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Slim\Views\Twig;

class LoginController extends AbstractTwigController
{
    /**
     * @var Preferences
     */
    private $preferences;
    /**
     * @var OauthclientProvider
     */
    private $oauthclientProvider;
    /**
     * LoginController constructor.
     *
     * @param Twig        $twig
     * @param Preferences $preferences
     */
    public function __construct(Twig $twig, Preferences $preferences , Session $session, FusionAuth $oauthclientProvider)
    {
        parent::__construct($twig);
        $this->oauthclientProvider = $oauthclientProvider;
        $this->preferences = $preferences;
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
        return $this->render($response, 'login.twig', [
            'pageTitle' => 'Login',
            'authorizationUrl' => $this->oauthclientProvider->getAuthorizationUrl(),
            'data' => $this->oauthclientProvider->getAuthorizationUrl(),
            'rootPath' => $this->preferences->getRootPath(),
        ]);
    }
}
