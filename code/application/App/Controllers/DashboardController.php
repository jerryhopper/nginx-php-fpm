<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Preferences;
use App\Runtime;
use JerryHopper\OAuth2\Client\Provider\FusionAuth;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Symfony\Component\HttpFoundation\Session\Session;

class DashboardController extends AbstractTwigController
{
    /**
     * @var Runtime
     */
    private $runtime;

    /**
     * DashboardController constructor.
     *
     * @param Twig        $twig
     * @param Runtime $runtime
     */
    public function __construct(Twig $twig, Runtime $runtime)
    {
        parent::__construct($twig);

        $this->runtime = $runtime;


        #$this->session = $runtime->getSession();
        #$this->preferences = $runtime->getPreferences();
        #$this->fusionauth = $runtime->getFusionAuth();

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
        // Runtime data (array)
        $RuntimeData = $this->runtime->data($request->getAttribute('token'));





        // return the templae.
        return $this->render($response, 'dashboard.twig', [
            'pageTitle' => 'dashboardController',
            'runtime'=> $RuntimeData,
        ]);
    }
}
