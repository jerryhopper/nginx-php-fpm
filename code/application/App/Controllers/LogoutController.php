<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Preferences;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class LogoutController extends AbstractTwigController
{
    /**
     * @var Preferences
     */
    private $preferences;

    /**
     * LogoutController constructor.
     *
     * @param Twig        $twig
     * @param Preferences $preferences
     */
    public function __construct(Twig $twig, Preferences $preferences)
    {
        parent::__construct($twig);

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
        $url = new \App\Action\LogoutAction;

        // GET /oauth2/logout?client_id={client_id}&tenantId={tenantId}
        //$url($session)= "";
        return $response->withStatus(302)->withHeader('Location', $url);
        #return $this->render($response, 'login.twig', [
        #    'pageTitle' => 'Logout',
        #    'rootPath' => $this->preferences->getRootPath(),
        #]);
    }
}
