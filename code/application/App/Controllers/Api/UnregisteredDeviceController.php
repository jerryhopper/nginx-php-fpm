<?php



namespace App\Controllers\Api;


use App\Controllers\AbstractTwigController;
use App\Database\Models\UnregisteredDevice;
use App\Database\Schemas\UnregisteredDeviceSchema;
use App\Preferences;
use App\ProjectCode\CfLocalDns;
use App\Runtime;
use App\Service\UnregisteredDeviceService;
use Illuminate\Support\Carbon;
use JerryHopper\OAuth2\Client\Provider\FusionAuth;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Symfony\Component\HttpFoundation\Session\Session;
use Illuminate\Database\Capsule\Manager as Capsule;


class UnregisteredDeviceController extends AbstractTwigController
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

    public function __construct(Twig $twig, Runtime $runtime, Capsule $capsule)
    {
        parent::__construct($twig);
        #$this->oauthclientProvider = $oauthclientProvider;
        #$this->preferences = $preferences;
        #$this->session = $session;
        #$this->cflocaldns = new CfLocalDns($this->preferences->getCloudflareToken(), $this->preferences->getCloudflareZoneId());

        $this->UnregisteredDeviceService = new UnregisteredDeviceService();

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
        # check if POST
        if( $request->getMethod()=="POST" ){
            return $this->POST($request,$response,$args);
        }
        # GET

        return $this->get($request,$response,$args);

    }

    private function GET(Request $request, Response $response, array $args = []){

        try{
            $res = $this->UnregisteredDeviceService->getHosts( $request->getAttribute('ip_address'));

        }catch(\Exception $e){

            if($e->getCode()=="42S02"){
                $this->UnregisteredDeviceService->createTable();
                $res = $this->UnregisteredDeviceService->getHosts( $request->getAttribute('ip_address'));
            }else{
                throw new \Exception($e->getMessage(),$e->getCode());
            }


        }
        //$res = $this->UnregisteredDeviceService->getHosts( $request->getAttribute('ip_address'));
        $response->getBody()->write(json_encode($res));

        return $response->withHeader('Content-Type', 'application/json');
    }
    private function POST(Request $request, Response $response, array $args = []){

        #$ipadress = $request->getQueryParams()['ipadress'];

        # deviceid

        if ( $request->getHeader("User-Agent")[0]=="OSBox" ){

        }

        $ipAddress = $request->getAttribute('ip_address');

        $data = (array)$request->getParsedBody();


        $net1 = (string)($data['eth0'] ?? '');
        $net2 = (string)($data['eth1'] ?? '');
        $deviceid = (string)($data['deviceid'] ?? '');

        $net1 = explode(",",$net1);
        $net2 = explode(",",$net2);


        $boxlist[$ipAddress] = $net2[0];




        $res = array(
            "eth0"=> $net1,
            "eth1"=> $net2,
            "extip"=>$ipAddress,
            "h"=>$request->getHeader("User-Agent"),
            "deviceid"=>$deviceid );




        try{
            $this->UnregisteredDeviceService->setIpInDb($net2[0],$ipAddress,$deviceid);
        }catch(\Exception $e){

            if($e->getCode()=="42S02"){
                $this->UnregisteredDeviceService->createTable();
                $this->UnregisteredDeviceService->setIpInDb($net2[0],$ipAddress,$deviceid);
            }else{
                throw new \Exception($e->getMessage(),$e->getCode());
            }


        }

        $this->UnregisteredDeviceService->deleteStaleRecords();

        $response->getBody()->write(json_encode($res));

        return $response->withHeader('Content-Type', 'application/json');
    }

}
