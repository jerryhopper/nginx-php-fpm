<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Preferences;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Symfony\Component\HttpFoundation\Session\Session;

class DashboardController extends AbstractTwigController
{
    /**
     * @var Preferences
     */
    private $preferences;

    /**
     * LoggedinController constructor.
     *
     * @param Twig        $twig
     * @param Preferences $preferences
     */
    public function __construct(Twig $twig, Preferences $preferences,Session $session)
    {
        parent::__construct($twig);

        $this->session = $session;

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
        $usr = $this->session->get('user');

        return $this->render($response, 'dashboard.twig', [
            'pageTitle' => 'loggedin',
            'user' => $usr,
            'rootPath' => $this->preferences->getRootPath(),
        ]);
    }
}
