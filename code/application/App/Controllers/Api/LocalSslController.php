<?php





namespace App\Controllers\Api;


use App\Controllers\AbstractTwigController;
use App\Preferences;
use App\ProjectCode\CfLocalDns;

use JerryHopper\OAuth2\Client\Provider\FusionAuth;

use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

use Symfony\Component\HttpFoundation\Session\Session;
use Illuminate\Database\Capsule\Manager as Capsule;

use Slim\Psr7\Factory\StreamFactory;


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

        if(!$item){
            return $response->withStatus(401);
        }
        # /home/nginx/.acme.sh/ssl.dockbox.nl/ssl.dockbox.nl.key
        # /home/nginx/.acme.sh/ssl.dockbox.nl/ssl.dockbox.nl.cer
        # /home/nginx/.acme.sh/ssl.dockbox.nl/fullchain.cer
        # /home/nginx/.acme.sh/ssl.dockbox.nl/ca.cer


        $filez = array(
            "key"=>"ssl.dockbox.nl.key",
            "cer"=>"ssl.dockbox.nl.cer",
            "fullchain"=>"fullchain.cer"
        );

        $dlfilename=$filez[$item];






        //        (new StreamFactory())->createStream($data)

        //$the_file=$_SERVER['DOCUMENT_ROOT']."info.php";

        $the_file = '/home/nginx/.acme.sh/ssl.dockbox.nl/'.$dlfilename;



        if( ! file_exists($the_file) ){
            return $response->withStatus(404);
        }


        //$the_file = $_SERVER['DOCUMENT_ROOT']."info.php";

        $handle = fopen($the_file, "r");
        $contents = fread($handle, filesize($the_file));
        fclose($handle);



        #var_dump(file_get_contents($the_file ));
        #$item = stream_get_contents($the_file);

        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $responseBody  = $psr17Factory->createStream($contents);




        $response = $response
            /*->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename='.$dlfilename)
            */
            ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache')
            ->withBody( $responseBody );

        return $response;
    }






}
