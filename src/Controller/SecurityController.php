<?php

namespace App\Controller;

use App\Service\SessionTokenManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
/**
 * @author Aléki <alexlegras@hotmail.com>
 * @version 1
 * Controller handling authentication and user security.
 */
class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'login')]
     /**
     * Displays the login form.
     *
     * @param AuthenticationUtils $authenticationUtils
     *
     * @return Response
     *
    */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout(SessionTokenManager $sessionTokenManager): void
    {   
        $sessionTokenManager->destroyApiToken();
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
