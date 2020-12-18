<?php


namespace App\Controllers\Api;


use App\Controllers\AbstractTwigController;
use App\Database\Models\LocalDns;
use App\Database\Models\UnregisteredDevice;
use App\Database\Schemas\LocalDnsSchema;
use App\Preferences;
use App\Service\CfLocalDns;
use JerryHopper\OAuth2\Client\Provider\FusionAuth;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Symfony\Component\HttpFoundation\Session\Session;
use Illuminate\Database\Capsule\Manager as Capsule;


use Illuminate\Database\Eloquent\ModelNotFoundException;


class LocalDnsController extends AbstractTwigController
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

        if( ! array_key_exists('ipadress',$request->getQueryParams()) ){
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }


        ## Get the ip adress from query-paramater
        $ipadress = $request->getQueryParams()['ipadress'];




        try{
            $users = $this->getIpFromDb($ipadress);
        }catch(\Exception $e){
            $users = array();



            // Table not found?  create it.
            if($e->getCode()=="42S02"){
                // create table!?
                LocalDnsSchema::create();
            }
        }

        if( count($users) > 0 ){
            $response->getBody()->write(json_encode( array("ip"=>$ipadress,"host"=>str_replace(".","-",$ipadress).".ssl.dockbox.nl","cache"=>true ) ));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        }

        #$response->getBody()->write(json_encode( array("users"=>$users ) ));
        #return $response->withStatus(500)->withHeader('Content-Type', 'application/json');



        //$users = $this->capsule->table('users')->where('votes', '>', 100)->get();


        $res = array();

        try{
            #$res = $this->cflocaldns->addPrivateIp($ipadress);
        }catch(\Exception $e){
            $lines = explode("\n",$e->getMessage());
            $json = json_decode($lines[1]);

            if($json->errors[0]->code!=81057){
                $response->getBody()->write(json_encode( array("json"=>$json->errors[0]->code,"code"=>$e->getCode(), "error"=>$e->getMessage()) ));
                return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
            }

        }

        try{
            $this->addIpInDb($ipadress);
        }catch(\Exception $e){
            if ( ! strpos($e->getMessage(),"dns_ip_unique")){
                $response->getBody()->write(json_encode( array("error"=>$e->getMessage()) ));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        }


        $response->getBody()->write(json_encode( array("ip"=>$ipadress,"host"=>str_replace(".","-",$ipadress).".ssl.dockbox.nl" ) ));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

    }


    private function addIpInDb($ipadress){
        return LocalDns::updateOrCreate([ 'ip' => $ipadress ]);
    }

    private function getIpFromDb($ipadress){
        return LocalDns::where('ip', $ipadress)->get();
    }



}
