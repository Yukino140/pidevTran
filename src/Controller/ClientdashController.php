<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientdashController extends AbstractController
{
    #[Route('/clientdash', name: 'app_clientdash')]
    public function index(): Response
    {
        return $this->render('clientdash/index.html.twig', [
            'controller_name' => 'ClientdashController',
        ]);
    }

    #[Route('/client',name:'client_app')]
    public function indexclient():Response
    {
        return $this->render('clientdash/client/client.html.twig');
    }

    #[Route('/clientPro',name:'clientPro_app')]
    public function indexclientPro():Response
    {
        return $this->render('clientdash/profile/client-profile.html.twig');
    }

    #[Route('/clientCon',name:'clientCon_app')]
    public function indexadminContact():Response
    {
        return $this->render('clientdash/contact/client-contact.html.twig');
    }

}
