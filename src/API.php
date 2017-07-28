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

    private $retried = false;

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

    // relay calls to tcdent/RestClient
    public function __call($name, $arguments){
        // check if method exists on RestClient
    	if( method_exists($this->api, $name) ){
    		// add access token to requestUrl if first try, else replace with new token
    		if(!$this->retried) {
                $arguments[0] .= '?access_token=' . $this->auth->getToken();
            } else {
                preg_replace("/\?.*/", "?access_token=" . $this->auth->getNewToken(), $arguments[0]);
            }
    		
            //call method on RestClient and decode the response, is decoded as json
            //call_user_func_array([onThisObject, methodName], arguments)
    		$response = call_user_func_array([$this->api, $name], $arguments)->decode_response();
    		
            // retry if not authorized
            if(isset($response->status)
    			&& isset($response->status->code)
    			&& $response->status->code == 401){

                $this->retried = true;
                return call_user_func_array([$this, $name], $arguments);

    		} else if($this->retried) {
                throw new UnauthorizedException();
            }
            $this->retried = false;
    		return $response;
    	} else {
            throw new OperationNotAvailableException();
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
