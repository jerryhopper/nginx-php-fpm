<?php


namespace App\Controllers\Api;


use App\Controllers\AbstractTwigController;
use App\Preferences;

use App\Service\CfLocalDns;
use App\Service\UnregisteredDeviceService;

use JerryHopper\OAuth2\Client\Provider\FusionAuth;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Symfony\Component\HttpFoundation\Session\Session;
use Illuminate\Database\Capsule\Manager as Capsule;

class StatusController extends AbstractTwigController
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
     * @var UnregisteredDeviceService
     */
    private $UnregisteredDeviceService;

    /**
     * LoginController constructor.
     *
     * @param Twig $twig
     * @param Preferences $preferences
     */

    public function __construct(Twig $twig, Preferences $preferences, Session $session, FusionAuth $oauthclientProvider)
    {
        parent::__construct($twig);
        $this->oauthclientProvider = $oauthclientProvider;
        $this->preferences = $preferences;
        $this->session = $session;
        //$this->cflocaldns = new CfLocalDns($this->preferences->getCloudflareToken(), $this->preferences->getCloudflareZoneId());

        $this->UnregisteredDeviceService = new UnregisteredDeviceService();

        //


    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $args = []): Response
    {

        #$ipadress = $request->getQueryParams()['ipadress'];
        #$data = (array)$request->getParsedBody();
        #$ipadress = (string)($data['ipadress'] ?? '');

        #$x= class_exists("Cloudflare\API\Auth\APIToken");
        #$key     = new \Cloudflare\API\Auth\APIToken ( $apitoken);
        #throw new \Exception(json_encode($x));

        #$key     = new CloudFlare\API\APIToken ( $apitoken);


        $res = array();

        /*
        try {
            //$res = $this->cflocaldns->addPrivateIp($ipadress);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(array("error" => $e->getMessage())));
            return $response->withStatus(500);
        }*/

        // json_encode($this->session->get('user')['token'] )
        $devices = array();
        try{
            $devices = $this->UnregisteredDeviceService->getHosts( $request->getAttribute('ip_address'));
        }catch(\Exception $e ){
            if($e->getCode()=="42S02"){
                $this->UnregisteredDeviceService->createTable();

            }else{
                throw new \Exception($e->getMessage());
            }
        }




        $output = array(    "registered" => array(),
                            "unregistered"=> $devices ,
                            "token"=>$this->session->get('user')['token'] );

        $response->getBody()->write(json_encode( $output ));

        return $response->withHeader('Content-Type', 'application/json');
        /*
                return $response->withHeader("Content-type","application/json; charset=utf-8")->withBody("[]");

                return $this->render($response, 'login.twig', [
                    'pageTitle' => 'Login',
                    'authorizationUrl' => $this->oauthclientProvider->getAuthorizationUrl(),
                    'data' => $this->oauthclientProvider->getAuthorizationUrl(),
                    'rootPath' => $this->preferences->getRootPath(),
                ]);*/
    }
}
