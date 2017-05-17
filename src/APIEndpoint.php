<?php

namespace GISwrapper;

/**
 * Class APIEndpoint
 *
 * @author Lukas Ehnle <lukas.ehnle@aiesec.de>
 * @version 0.3
 * @package GISwrapper
 */
class APIEndpoint implements \ArrayAccess
{
	private $api;

	private $parts;

	private $subs;

	private $params;

	/**
     * BaseOperations constructor.
     */
    function __construct($parts = [], $newPart = NULL)
    {
        $this->parts = $parts;
        if($newPart){
        	$this->parts[] = $newPart;
        }

        $this->api = API::getInstance();
        $this->subs = array();
		$this->params = new Param();
    }

	public function get(){
		return $this->api->get(
            implode('/', $this->parts),
            $this->params->value()
        );
	}

	public function patch(){
		return $this->api->patch(
            implode('/', $this->parts),
            json_encode($this->params->value())
        );
	}

	public function post(){
		return $this->api->post(
            implode('/', $this->parts),
            json_encode($this->params->value())
        );
	}

	public function delete(){
		return $this->api->delete(
            implode('/', $this->parts),
            json_encode($this->params->value())
        );
	}

	/**
     * @param mixed $name property name
     * @return mixed|null property value
     */
    public function __get($name)
    {
    	if(!isset($this->subs[$name])){
    		$this->subs[$name] = new APIEndpoint($this->parts, $name);
    	}
    	return $this->subs[$name];
    }

    /**
     * @param string $name name of the property to change
     * @param array $arr new values for the property
     * @return void
     */
    public function __set($name, $arr)
    {
    	if(is_array($arr)){
    		$sub = $this->__get($name);
    		foreach ($arr as $key => $value) {
    			$sub[$key] = $value;
    		}
    	} else {
    		trigger_error("Use array notation to assign parameters.", E_USER_ERROR);
    	}
    }

    /**
     * @param mixed $offset
     * @return bool indicating if this offset is instantiated
     */
    public function offsetExists($offset)
    {
        return isset($this->params[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed returns the instance at this offset
     */
    public function offsetGet($offset)
    {
        if(!isset($this->params[$offset])){
            $this->params[$offset] = new Param();
        }
        return $this->params[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->params[$offset] = $value;
    }

    /**
     * @param mixed $offset offset of the instance to be destroyed
     */
    public function offsetUnset($offset)
    {
        unset($this->params[$offset]);
    }

}
