<?php
namespace GISwrapper;

/**
 * Class Param
 *
 * @author Lukas Ehnle <lukas.ehnle@aiesec.de>
 * @package GISwrapper
 * @version 0.3
 */
class Param implements \ArrayAccess
{
	private $values;

	public function __construct()
    {
        $this->values = [];
    }

    public function value()
    {
    	$arr = [];
    	foreach ($this->values as $key => $value) {
    		$arr[$key] = $value->value();
    	}
    	return $arr;
    }

	/**
     * @param mixed $offset
     * @return bool indicating if this offset is instantiated
     */
    public function offsetExists($offset)
    {
        return isset($this->values[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed returns the instance at this offset
     */
    public function offsetGet($offset)
    {
        if(!isset($this->values[$offset])){
            $this->values[$offset] = new Param();
        }
        return $this->values[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if(is_array($value)){
        	foreach ($value as $key => $val) {
        		$this->values[$offset][$key] = $val;
        	}
        } else {
        	$this->values[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset offset of the instance to be destroyed
     */
    public function offsetUnset($offset)
    {
        unset($this->values[$offset]);
    }

}
