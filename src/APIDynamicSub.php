<?php
/**
 * Created by PhpStorm.
 * User: kjs
 * Date: 24.05.16
 * Time: 17:38
 */

namespace GISwrapper;


class APIDynamicSub extends API implements \ArrayAccess
{
    private $_dynamicSub;

    public function __construct($cache, $auth, $pathParams = array())
    {
        parent::__construct($cache, $auth, $pathParams);
        $this->_dynamicSub = new DynamicSub($cache, $auth, $pathParams);
    }

    public function exists($name) {
        if(!$this->existsSub($name)) {
            return $this->existsDynamicSub($name);
        } else {
            return true;
        }
    }

    public function existsSub($name) {
        return isset($this->_cache['subs'][$name]);
    }

    public function existsDynamicSub($name) {
        return $this->_dynamicSub->exists($name);
    }

    public function offsetExists($offset)
    {
        return $this->_dynamicSub->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->_dynamicSub->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->_dynamicSub->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->_dynamicSub->offsetUnset($offset);
    }
}