<?php

declare(strict_types=1);

namespace App;


use JerryHopper\OAuth2\Client\Provider\FusionAuth;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;


/**
 * This class contains the runtime preferences for the application.
 *
 * @package App
 */
class Runtime
{
    /**
     * @var object
     */
    private $fusionAuth;
    private $preferences;
    private $session;


    /**
     * Preferences constructor.
     *
     * @param string $rootPath
     */
    public function __construct(ContainerInterface $container)
    {
        $this->fusionAuth = $container->get(FusionAuth::class);
        $this->preferences = $container->get(Preferences::class);
        $this->session = $container->get(Session::class);
    }


    /**
     * @return object
     */
    public function getPreferences(): object
    {
        return $this->preferences;
    }

    /**
     * @return object
     */
    public function getFusionAuth(): object
    {
        return $this->fusionAuth;
    }
    /**
     * @return object
     */
    public function getSession(): object
    {
        return $this->session;
    }

    public function data($tokeninfo): array {

        $array=array();


        if(!$tokeninfo){
            $array['isAuthenticated']=false;
        }else{
            $array['isAuthenticated']=$tokeninfo['valid'];
        }


        $array['auth']=array(
            "AuthorizationUrl"=>$this->fusionAuth->getAuthorizationUrl(),
        );

        $array['rootPath'] = $this->preferences->getRootPath();


        if($tokeninfo['valid']){
            $array['token']=array(
                "rawToken"=>$tokeninfo['rawToken'],
                "tokenFrom"=>$tokeninfo['tokenFrom'],
                "payload"=>$tokeninfo['payload']->__debugInfo()
            );

            $array['user']=array(
                "username"=>$array['token']['payload']['preferred_username'],
                "email"=>$array['token']['payload']['email'],
            );

        }else{
            $array['token']=false;
            $array['user']=false;
        }
        return $array;
    }

    /*
        #$this->session = $runtime->getSession();
        #$this->preferences = $runtime->getPreferences();
        #$this->fusionauth = $runtime->getFusionAuth();

        #$this->fusionauth->getAuthorizationUrl();

        //$runtime->getFusionAuth()->getAuthorizationUrl();
        //print_r($runtime->getPreferences());
        #$this->fusionauth->getState();
        #$this->fusionauth->getResourceOwnerDetailsUrl();
        #$this->fusionauth->
    */

}
