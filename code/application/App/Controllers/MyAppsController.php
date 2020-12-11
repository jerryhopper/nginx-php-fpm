<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Preferences;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Symfony\Component\HttpFoundation\Session\Session;

use JerryHopper\OAuth2\Client\Provider\FusionAuth;


class MyAppsController extends AbstractTwigController
{
    /**
     * @var Preferences
     */
    private $preferences;

    /**
     * MyAppsController constructor.
     *
     * @param Twig        $twig
     * @param Preferences $preferences
     */
    public function __construct(Twig $twig, Preferences $preferences,Session $session , FusionAuth $oauthclientProvider )
    {
        parent::__construct($twig);
        $this->session = $session;
        $this->preferences = $preferences;

        $this->oauthclientProvider = $oauthclientProvider;
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
        #$usr = $this->session->get('user');
        #$usr = $this->session->get('authenticated');

        return $this->render($response, 'myapps.twig', [
            'pageTitle' => 'My Apps',
            'authorizationUrl' => $this->oauthclientProvider->getAuthorizationUrl(),
            'user' => $this->session->all(),
            'data' => $this->oauthclientProvider->getAuthorizationUrl(),
            'rootPath' => $this->preferences->getRootPath(),
        ]);
    }
}
