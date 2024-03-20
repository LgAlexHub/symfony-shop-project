<?php 

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @author Aléki <alexlegras@hotmail.com>
 * @version 1
 * This service is use to set a personnal token to an user session
 * This token will be use for api request in back office to provide 
 * any non logged user from accessing to those api route
 */
class SessionTokenManager {

    public function __construct(
        private RequestStack $requestStack
    ){
        
    }

    /**
     * This method retreive the user's session
     *
     * @return SessionInterface
     */
    public function getSession() : SessionInterface{
        return $this->requestStack->getCurrentRequest()->getSession();
    }

    /**
     * Will set a uuid token to current user session
     *
     * @return void
     */
    public function setApiToken(){
        $this->getSession()->set("api_token", Uuid::v4()->toBase32());
    }

    /**
     * Will try to retreive api token from current user's session 
     *
     * @return string|null either return token in string or null if no token is set
     */
    public function getApiToken() : string|null {
        return $this->getSession()->get('api_token');
    }

    /**
     * Will delete token from current session
     *
     * @return void
     */
    public function destroyApiToken(){
        $this->getSession()->set('api_token', null);
    }
}