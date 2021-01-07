<?php



namespace App\Controllers\Api;


use App\Controllers\AbstractTwigController;
use App\Database\Models\UnregisteredDevice;
use App\Database\Schemas\UnregisteredDeviceSchema;
use App\Preferences;
use App\ProjectCode\CfLocalDns;
use App\Runtime;
use App\Service\RegisteredDeviceService;
use App\Service\UnregisteredDeviceService;
use Illuminate\Support\Carbon;
use JerryHopper\OAuth2\Client\Provider\FusionAuth;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Symfony\Component\HttpFoundation\Session\Session;
use Illuminate\Database\Capsule\Manager as Capsule;


class RegisteredDeviceController extends AbstractTwigController
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
        //$this->oauthclientProvider = $oauthclientProvider;
        //$this->preferences = $preferences;
        //$this->session = $session;
        #$this->cflocaldns = new CfLocalDns($this->preferences->getCloudflareToken(), $this->preferences->getCloudflareZoneId());

        $this->RegisteredDeviceService = new RegisteredDeviceService();



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

        $user = $this->session->get('user');
        $owner = $user->userinfo->tokeninfo->userId;

        #$owner="0629a012-69f3-45d0-88b5-a7c4527c828e";

        try{
            $res = $this->RegisteredDeviceService->getHosts( $owner  );

        }catch(\Exception $e){

            if($e->getCode()=="42S02"){
                $this->RegisteredDeviceService->createTable();
                $res = $this->RegisteredDeviceService->getHosts( $owner );
            }else{
                throw new \Exception($e->getMessage(),$e->getCode());
            }


        }
        //$res = $this->RegisteredDeviceService->getHosts( $request->getAttribute('ip_address'));
        $response->getBody()->write(json_encode($res));

        return $response->withHeader('Content-Type', 'application/json');
    }


    private function POST(Request $request, Response $response, array $args = []){

        #$ipadress = $request->getQueryParams()['ipadress'];


        $user = $this->session->get('user');
        $owner = $user->userinfo->tokeninfo->userId;

        # deviceid

        if ( $request->getHeader("User-Agent")[0]=="OSBox" ){

        }

        #$ipAddress = $request->getAttribute('ip_address');

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
            "int-ip"=>$net2[0],
            "h"=>$request->getHeader("User-Agent"),
            "deviceid"=>$deviceid );


        try{
            //$intIP,$owner,$deviceid
            $this->RegisteredDeviceService->setIpInDb($net2[0],$owner,$deviceid);

        }catch(\Exception $e){
            #throw new \Exception($e->getMessage(),$e->getCode());

            if($e->getCode()=="42S02" ||$e->getCode()=="42"  ){
                #
                $this->RegisteredDeviceService->createTable();
                $this->RegisteredDeviceService->setIpInDb($net2[0],$ipAddress,$deviceid);
            }else{
                throw new \Exception($e->getMessage(),$e->getCode());
            }

        }
        $this->RegisteredDeviceService->deleteStaleRecords();

        $response->getBody()->write(json_encode($res));

        return $response->withHeader('Content-Type', 'application/json');
    }

}
