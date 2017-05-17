<?php
namespace GISwrapper;

/**
 * Class GIS
 * entry point to the GIS
 *
 * @author Lukas Ehnle <lukas.ehnle@aiesec.de>
 * @package GISwrapper
 * @version 0.3
 */
class GIS
{
    private $api;

    /**
     * GIS constructor.
     * @param AuthProvider $auth
     * @throws InvalidAuthProviderException
     * @throws NoResponseException
     * @throws RequirementsException
     */
    function __construct($auth)
    {
        // check that $auth implements the AuthProvider interface
        if ($auth instanceof AuthProvider) {
            API::createInstance($auth);
            $this->api = new APIEndpoint();
        } else {
            throw new InvalidAuthProviderException("The given object does not implement the AuthProvider interface.");
        }
    }

    /**
     * @param mixed $name property name
     * @return mixed value of the property
     * @throws NoResponseException
     * @throws RequirementsException
     */
    public function __get($name)
    {
        return $this->api->__get($name);
    }

    /**
     * @param mixed $name name of the property
     * @param mixed $value value to set for the property
     * @throws NoResponseException
     * @throws RequirementsException
     */
    public function __set($name, $value) {
        return $this->api->__set($name, $value);
    }

    /**
     * @param $name property name
     * @return bool indicating if the property is instantiated
     */
    public function __isset($name)
    {
        return $this->api->__isset($name);
    }

    /**
     * @param $name property name of the instance to be destroyed
     */
    public function __unset($name)
    {
        $this->api->__unset($name);
    }

}
