<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
class AuthController extends AbstractController
{
    #[Route('/api/check-auth', name: 'api_check_auth')]
    public function checkAuth(Security $security): JsonResponse
    {
        $user = $security->getUser();
        return new JsonResponse(['isAuthenticated' => $user !== null]);
    }
}