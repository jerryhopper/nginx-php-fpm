<?php

declare(strict_types=1);

namespace App;

/**
 * This class contains the preferences for the application.
 *
 * @package App
 */
class Preferences
{
    /**
     * @var string
     */
    private $rootPath;

    private $oauthDiscoveryurl;


    private $oauthClientid;
    private $oauthClientsecret;
    private $oauthTenantid;

    private $cloudflareToken;
    private $cloudflareZoneId;


    /**
     * Preferences constructor.
     *
     * @param string $rootPath
     */
    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;

        # local dev

        $this->localdevelopment = $_SERVER["LOCAL_DEVELOPMENT"] ? true : false;


        # CloudFlare
        $this->cloudflareToken = $_SERVER["CF_TOKEN"] ? $_SERVER["CF_TOKEN"] : null;
        $this->cloudflareZoneId = $_SERVER["CF_ZONE_ID"] ? $_SERVER["CF_ZONE_ID"] : null;


        # Oauth DiscoveryUrl
        $this->oauthDiscoveryurl = $_SERVER["OAUTH_DISCOVERY"] ? $_SERVER["OAUTH_DISCOVERY"] : null;


        $this->oauthClientid = $_SERVER["OAUTH_CLIENT_ID"] ? $_SERVER["OAUTH_CLIENT_ID"] : null;
        $this->oauthClientsecret = $_SERVER["OAUTH_CLIENT_SECRET"] ? $_SERVER["OAUTH_CLIENT_SECRET"] : null;

        $this->oauthTenantid = $_SERVER["OAUTH_TENANT_ID"] ? $_SERVER["OAUTH_TENANT_ID"] : null;

        $this->oauthRedirectUrl = $_SERVER["OAUTH_REDIR_URL"] ? $_SERVER["OAUTH_REDIR_URL"] : null;



        # Database
        $this->db_host = $_SERVER["DB_HOST"] ? $_SERVER["DB_HOST"] : null;
        $this->db_port = $_SERVER["DB_PORT"] ? $_SERVER["DB_PORT"] : null;
        $this->db_user = $_SERVER["DB_USER"] ? $_SERVER["DB_USER"] : null;
        $this->db_pass = $_SERVER["DB_PASS"] ? $_SERVER["DB_PASS"] : null;
        $this->db_dbname = $_SERVER["DB_DBNAME"] ? $_SERVER["DB_DBNAME"] : null;

        # Add $_SERVER['REMOTE_ADDR'] for use with docker, else it will resolve the docker-ip
        $this->trusted_proxies = array($_SERVER['REMOTE_ADDR']);
        #$this->trusted_proxies = explode( ",", $_SERVER["TRUSTED_PROXIES"] );

    }


    /**
     *
     *
     */
    public function isLocallyHosted(): array
    {
        return $this->localdevelopment;
    }

    /**
     * @return array
     */
    public function getTrustedProxies(): array
    {
        return $this->trusted_proxies;
    }

    /**
     * @return string
     */
    public function getDbName(): string
    {
        return $this->db_dbname;
    }
    /**
     * @return string
     */
    public function getDbPass(): string
    {
        return $this->db_pass;
    }
    /**
     * @return string
     */
    public function getDbUser(): string
    {
        return $this->db_user;
    }
    /**
     * @return string
     */
    public function getDbPort(): string
    {
        return $this->db_port;
    }
    /**
     * @return string
     */
    public function getDbHost(): string
    {
        return $this->db_host;
    }




    /**
     * @return string
     */
    public function getCloudflareToken(): string
    {
        return $this->cloudflareToken;
    }
    /**
     * @return string
     */
    public function getCloudflareZoneId(): string
    {
        return $this->cloudflareZoneId;
    }





    /**
     * @return string
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }
    /**
     * @return string
     */
    public function getOauthRedirectUrl(): string
    {
        return $this->oauthRedirectUrl;
    }


    /**
     * @return string
     */
    public function getSessionSettings(): string
    {
        return array('name' => 'webapp','cache_expire' => 0);
    }


    /**
     * @return string
     */
    public function getOauthDiscoveryurl(): string
    {
        return $this->oauthDiscoveryurl;
    }
    /**
     * @return string
     */
    public function getOauthClientid(): string
    {
        return $this->oauthClientid;
    }
    /**
     * @return string
     */
    public function getOauthClientsecret(): string
    {
        return $this->oauthClientsecret;
    }
    /**
     * @return string
     */
    public function getOauthTenantid(): string
    {
        return $this->oauthTenantid;
    }

}
