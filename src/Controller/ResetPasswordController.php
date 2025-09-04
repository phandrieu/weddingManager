<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResetPasswordController extends AbstractController
{
    #[Route('/reset-password/request', name: 'app_forgot_password')]
    public function request(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                // Générer un token
                $token = Uuid::v4()->toRfc4122();
                $user->setResetToken($token);
                $em->flush();

                $resetUrl = $this->generateUrl(
    'app_reset_password',
    ['token' => $token],
    UrlGeneratorInterface::ABSOLUTE_URL
);

$emailMessage = (new TemplatedEmail())
    ->from('paul-henri.andrieu@centrale-med.fr')
    ->to($user->getEmail())
    ->subject('Réinitialisation de votre mot de passe')
    ->htmlTemplate('emails/reset_password.html.twig')
    ->context([
        'resetUrl' => $resetUrl,
        'user' => $user,
    ]);

$mailer->send($emailMessage);
                
            }

            $this->addFlash('success', 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/reset-password/reset/{token}', name: 'app_reset_password')]
    public function reset(
        string $token,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('Token invalide');
        }

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $user->setPassword($passwordHasher->hashPassword($user, $password));
            $user->setResetToken(null);
            $em->flush();

            $this->addFlash('success', 'Mot de passe réinitialisé avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'token' => $token,
        ]);
    }
}