<?php
namespace GISwrapper;

/**
 * Class API
 * make requests to API
 *
 * @author Lukas Ehnle <lukas.ehnle@aiesec.de>
 * @package GISwrapper
 * @version 0.3
 */
class API
{
	private static $instance;

	private $auth;

	private $api;

	private function __construct(AuthProvider $auth, String $baseUrl)
    {
        $this->auth = $auth;
        $this->api = new \RestClient([
		    'base_url' => $baseUrl,
		    'headers' => [
		    	'Content-Type' => 'application/json'
		    ]
		]);
    }

    public function __call($name, $arguments){
    	if( method_exists($this->api, $name) ){
    		// add access token to requestUrl
    		$arguments[0] .= '?access_token=' . $this->auth->getToken();
    		
    		$response = call_user_func_array([$this->api, $name], $arguments)->decode_response();
    		if(isset($response->status)
    			&& isset($response->status->code)
    			&& $response->status->code == 401){
    			//TODO: retry with new token on fail
    		}
    		return $response;
    	}
    }

    public static function createInstance($auth = NULL, $baseUrl = 'https://gis-api.aiesec.org/v2'){
    	if($auth instanceof AuthProvider){
    		API::$instance = new API($auth, $baseUrl);
    	}
    }

    public static function getInstance(){
    	if(! API::$instance instanceof API){
    		throw new \Error("API not instantiated yet.");
    	}
    	return API::$instance;
    }

}
