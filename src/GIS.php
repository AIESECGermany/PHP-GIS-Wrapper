<?php
namespace GISwrapper;

/**
 * Class GIS
 * entry point to the GIS
 *
 * @author Lukas Ehnle <me@ehnle.fyi>
 * @package GISwrapper
 * @version 0.3
 */
class GIS extends \OPRestclient\Client
{   
    private $auth;

    // needed in data container, to load paged endpoints
    public $lastUrl;

    /**
     * GIS constructor.
     * @param AuthProvider $auth
     * @throws InvalidAuthProviderException
     * @throws InvalidAuthResponseException
     * @throws InvalidCredentialsException
     */
    function __construct($auth, $baseUrl = 'https://gis-api.aiesec.org/v2')
    {
        // check that $auth implements the AuthProvider interface
        if ($auth instanceof AuthProvider) {
            parent::__construct([
                'base_url' => $baseUrl,
                'parameters' => [
                    'access_token' => $auth->getToken()
                ],
                'decoders' => ['json' => [$this, "decode"]]
            ]);
            $this->auth = $auth;
        } else {
            throw new InvalidAuthProviderException("The given object does not implement the AuthProvider interface.");
        }
    }

    /**
     * overwrite parse_response, so errors can be handled and the response be decoded by default
     * @param  GIS $res the GIS instance containing the response
     * @return Object      the decoded response
     */
    public function execute($url, $method='GET', $parameters=[], $headers=[]){
        $this->lastUrl = $url;
        $res = parent::execute($url, $method, $parameters, $headers);
        if($res->error){
            var_dump("Error");
        }
        return $res->decode_response();
    }

    protected function decode($res){
        $json = json_decode($res);
        if(isset($json->data) && isset($json->paging)){
            return new DataContainer($json, $this);
        } else {
            return $json;
        }
    }
}
