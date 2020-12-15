<?php





namespace App\Controllers\Api;


use App\Controllers\AbstractTwigController;
use App\Preferences;
use App\ProjectCode\CfLocalDns;
use JerryHopper\OAuth2\Client\Provider\FusionAuth;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Symfony\Component\HttpFoundation\Session\Session;
use Illuminate\Database\Capsule\Manager as Capsule;

class LocalSslController extends AbstractTwigController
{
    /**
     * @var Session
     */
    private $session;
    /**
     * @var Preferences
     */
    private $preferences;
    /**
     * @var OauthclientProvider
     */
    private $oauthclientProvider;

    /**
     * @var cflocaldns
     */
    private $cflocaldns;


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
        $this->session = $session;
        $this->cflocaldns = new CfLocalDns($this->preferences->getCloudflareToken(),$this->preferences->getCloudflareZoneId());

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

        #if( ! array_key_exists('ipadress',$request->getQueryParams()) ){
        #    return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        #}


        $item = $request->getQueryParams()['item']; // ssl.dockbox.nl.cer"



        $response->getBody()->write( json_encode( $item ) );

        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

    }






}
