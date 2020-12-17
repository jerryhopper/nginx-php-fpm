<?php



namespace App\Controllers\Api;


use App\Controllers\AbstractTwigController;
use App\Database\Models\UnregisteredDevice;
use App\Database\Schemas\UnregisteredDeviceSchema;
use App\Preferences;
use App\ProjectCode\CfLocalDns;
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
     * @var cflocaldns
     */
    private $cflocaldns;

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
        #$this->cflocaldns = new CfLocalDns($this->preferences->getCloudflareToken(), $this->preferences->getCloudflareZoneId());




    }

    public function getIpsFromDb($ipadress){
        return UnregisteredDevice::where('ext-ip', $ipadress)->get();
        #return Capsule::table('unregdevice')->where('ext-ip', '=', $ipadress)->get();
    }

    public function setIpInDb($intIP,$extIP){
        return UnregisteredDevice::updateOrCreate([    'id' => $extIP."-".$intIP,    'ext-ip' => $extIP,    'int-ip' => $intIP, ]);
        #return Capsule::table('unregdevice')->insert([ 'id'=>$extIP.'-'.$intIP , 'int-ip'=>$intIP , 'ext-ip' => $extIP , ]);
    }

    public function deleteStaleRecords(){
        UnregisteredDevice::where('updated_at', '<' , Carbon::now()->subDay())->delete();
    }


    public function createTable (){
        return UnregisteredDeviceSchema::create();
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
        $res = $this->getIpsFromDb( $request->getAttribute('ip_address'));
        $response->getBody()->write(json_encode($res));

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

    private function POST(Request $request, Response $response, array $args = []){

        #$ipadress = $request->getQueryParams()['ipadress'];


        if ( $request->getHeader("User-Agent")[0]=="OSBox" ){

        }

        $ipAddress = $request->getAttribute('ip_address');

        $data = (array)$request->getParsedBody();
        $net1 = (string)($data['eth0'] ?? '');
        $net2 = (string)($data['eth1'] ?? '');


        $net1 = explode(",",$net1);
        $net2 = explode(",",$net2);


        $boxlist[$ipAddress] = $net2[0];




        $res = array(   "eth0"=> $net1,
            "eth1"=> $net2,
            "extip"=>$ipAddress,
            "h"=>$request->getHeader("User-Agent") );


        $this->setIpInDb($net2[0],$ipAddress);
        try{


        }catch(\Exception $e){

            if($e->getCode()=="42S02"){
                //$this->createTable();
                //$this->setIpInDb($net2[0],$ipAddress);
            }


        }

        $this->deleteStaleRecords();

        $response->getBody()->write(json_encode($res));

        return $response->withHeader('Content-Type', 'application/json');
    }

}
