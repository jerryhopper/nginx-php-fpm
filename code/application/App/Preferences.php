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


    /**
     * Preferences constructor.
     *
     * @param string $rootPath
     */
    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;


        # CloudFlare
        $this->cloudflareToken = $_SERVER["CF_TOKEN"] ? $_SERVER["CF_TOKEN"] : null;
        $this->cloudflareZoneId = $_SERVER["CF_ZONEID"] ? $_SERVER["CF_ZONEID"] : null;

        # DiscoveryUrl
        $this->oauthDiscoveryurl = $_SERVER["OAUTH_DISCOVERY"] ? $_SERVER["OAUTH_DISCOVERY"] : null;


        $this->oauthClientid = $_SERVER["OAUTH_CLIENT_ID"] ? $_SERVER["OAUTH_CLIENT_ID"] : null;
        $this->oauthClientsecret = $_SERVER["OAUTH_CLIENT_SECRET"] ? $_SERVER["OAUTH_CLIENT_SECRET"] : null;

        $this->oauthTenantid = $_SERVER["OAUTH_TENANT_ID"] ? $_SERVER["OAUTH_TENANT_ID"] : null;

        $this->oauthRedirectUrl = $_SERVER["OAUTH_REDIR_URL"] ? $_SERVER["OAUTH_REDIR_URL"] : null;

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
