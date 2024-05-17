<?php

namespace App\Controller\Admin\Api;

use App\Service\SessionTokenManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiAdminController extends AbstractController{

    /**
     * This method check if our user have the bearer token to gain access to api
     *
     * @param Request $request
     * @param SessionTokenManager $sessionTokenManager
     * @return void
     */
    protected function checkBearerToken(Request $request, SessionTokenManager $sessionTokenManager){
        //Check if bearer token is in request header
        $bearerToken = $request->headers->get('authorization');
        if (is_null($bearerToken)){
            return $this->json("Unauthorized", 401);
        }
        //Check if bearer token is same as the one in the session
        $bearerToken = explode(" ", $bearerToken)[1];
        if ($sessionTokenManager->getApiToken() !== $bearerToken){
            return $this->json("Unauthorized", 401);
        }
        return null;
    }

    /**
     * Not necessary but save some time 
     *
     * @param string $json
     * @param integer $status
     * @return Response
     */
    protected function apiJson(string $json, int $status=200) : Response {
        return new Response($json, status: $status, headers: [
            'Content-Type' => 'application/json'
        ]); 
    }
}