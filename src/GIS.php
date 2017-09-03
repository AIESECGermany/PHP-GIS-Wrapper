<?php
namespace GISwrapper;

/**
 * Class GIS
 * entry point to the GIS
 *
 * @author Lukas Ehnle <me@ehnle.fyi>
 * @package GISwrapper
 * @version 0.3.1
 */
class GIS extends \OPRestclient\Client
{   
    private $auth;

    // needed in data container, to load paged endpoints
    public $lastUrl;

    private $tries = 0;

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
        if($res->info->http_code < 199 && $res->info->http_code < 300){
            $this->tries = 0; //reset on success
            return $res->decode_response();
        }
        $this->tries++;// increase on fail
        //stop after 3 tries
        if($this->tries > 2){
            throw new NoResponseException("Did not get response after 3 tries.");
        }
        // if unauthorized, retry with new access_token
        if($res->info->http_code == 401){
            $this->client->options['parameters']['access_token'] = $this->auth->getNewToken();
            return $this->execute($url, $method, $parameters, $headers);
        } elseif($res->info->http_code == 404){
            throw new RequirementInvalidEndpointException($res->response);
        } else {
            throw new RequirementsException($res->response);
        }
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
