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
        $this->cflocaldns = new CfLocalDns($this->preferences->getCloudflareToken(), $this->preferences->getCloudflareZoneId());


    }

    public function getIpFromDb($ipadress){
        return Capsule::table('unregdevice')->where('ext-ip', '=', $ipadress)->get();
    }

    public function setIpInDb($intIP,$extIP){
        return Capsule::table('unregdevice')->insert([ 'id'=>$extIP.'-'.$intIP , 'int-ip'=>$intIP , 'ext-ip' => $extIP ]);
    }

    public function createTable (){
        Capsule::schema()->create('unregdevice', function ($table) {
            $table->string('id')->unique();
            $table->string('ext-ip');
            $table->string('int-ip');
            $table->timestamps();
        });
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
        ##
        ##
        ##
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


        Capsule::schema()->create('unregdevice', function ($table) {
            $table->string('id')->unique();
            $table->string('ext-ip');
            $table->string('int-ip');
            $table->timestamps();
        });

        try{
            //$this->setIpInDb($net2[0],$ipAddress);

        }catch(\Exception $e){

            if($e->getCode()=="42S02"){
                //$this->createTable();
                //$this->setIpInDb($net2[0],$ipAddress);
            }


        }

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


}
