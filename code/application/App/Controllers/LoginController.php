<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Preferences;
use App\Runtime;
use JerryHopper\OAuth2\Client\Provider\FusionAuth;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Slim\Views\Twig;

class LoginController extends AbstractTwigController
{
    /**
     * @var Runtime
     */
    private $runtime;

    /**
     * LoginController constructor.
     *
     * @param Twig        $twig
     * @param Runtime $runtime
     */
    public function __construct(Twig $twig, Runtime $runtime)
    {
        parent::__construct($twig);
        $this->runtime = $runtime;

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
        return $this->render($response, 'login.twig', [
            'pageTitle' => 'loginController',
            'runtime'=> $RuntimeData,
        ]);
    }
}
