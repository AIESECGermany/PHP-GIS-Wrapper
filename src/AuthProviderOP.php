<?php
namespace GISwrapper;

/**
 * Class AuthProviderOP
 *
 * @author Karl Johann Schubert <karljohann@familieschubi.de>
 * @version 0.2
 * @package GISwrapper
 */
class AuthProviderOP extends AuthProviderCombined {

    /**
     * generateNewToken()
     *
     * function that performs a login with GIS auth to get a new OP access token, without validating the token
     *
     * @throws InvalidCredentialsException if the username or password is invalid
     */
    protected function generateNewToken() {
        // set type to OP
        $this->_type = false;

        // run the GIS Auth Flow for OP
        $this->GISauthFlow('https://auth.aiesec.org/oauth/authorize?redirect_uri=https%3A%2F%2Faiesec.org%2Fauth&response_type=code&client_id=e305a9957ba043d9998a2369064635cab525b4dfb8a7c8eb39035451236ffb67');
    }
}