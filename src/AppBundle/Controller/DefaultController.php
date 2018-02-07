<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Payum\Core\Request\GetHumanStatus;
use AppBundle\Entity\User;
use AppBundle\Entity\UserToken;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="register")
     * @Template("default/home.html.twig")
     */
    public function homeAction(Request $request)
    {
        $defaultData = [];
        $form = $this->createFormBuilder($defaultData)
                     ->add('email', EmailType::class)
                     ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $user_email = $form->getData()['email'];
            $user = $this->getDoctrine()
                         ->getRepository(User::class)
                         ->findOneBy([
                             'email' => $form->getData()['email']
                         ]);

            if (!$user) {
                // user not found -- create a new one
            } else {
                // user found
                // 1. Generate for him/her a new code
                // 2. Send an email
            }
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/verify/{code}", name="verify-email")
     */
    public function verifyEmailAction(Request $request, $code)
    {
        $token = $this->getDoctrine()
                      ->getRepository(UserToken::class)
                      ->findOneBy([
                          'token' => $code
                      ]);

        if (!$token) {
            throw $this->createNotFoundException();
        }

        $email = $token->getEmail();

        // remove the token
        $em = $this->getDoctrine()->getManager();
        $em->remove($token);
        $em->flush();

        // log in user
        $session = new Session();
        $session->migrate(true, 0);
        $session->set('logged', true);
        $session->set('email', $email);

        return $this->redirectToRoute('updateinfo');
    }

    /**
     * @Route("/me", name="updateinfo")
     * @Template("default/register.html.twig")
     */
    public function updateInfoAction(Request $request)
    {
        $session = new Session();

        if (!$session->get('logged')) {
            throw $this->createNotFoundException();
        }

        $email = $session->get('email');

        $user = $this->getDoctrine()
                     ->getRepository(User::class)
                     ->findOneBy(['email' => $email]);

        if (!$user) {
            // it's the first time the user logs in. It still does not
            // have a row in the db
            $user = new User();
            $user->setEmail($email);
        }

        $form = $this->createFormBuilder($user)
                     ->add('name', TextType::class, ['label' => "Nome e cognome"])
                     ->add('birth', DateType::class, [
                         'label' => 'Data di nascita',
                         'years' => range(date('Y'), 1900)
                     ])
                     ->add('email', EmailType::class, ['label' => 'Email'])
                     ->add('job', ChoiceType::class, [
                         'label'   => 'Professione',
                         'choices' => [
                             'Studente (fino a scuole superiori)' => User::SUPERIORI,
                             'Studente universitario'             => User::UNIVERSITA,
                             'Lavoratore'                         => User::LAVORATORE
                         ]
                     ])
                     ->add('address', TextType::class, [
                         'label' => 'Indirizzo',
                     ])
                     ->add('city', TextType::class, [
                         'label' => 'Città',
                     ])
                     ->getForm()
              ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // update the user
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->set('success', 'Informazioni aggiornate con successo!');
            return $this->redirectToRoute('updateinfo');
        }

        return ['form' => $form->createView()];
    }

    // /**
    //  * @Route("/", name="register")
    //  * @Template("default/register.html.twig")
    //  */
    // public function registerAction(Request $request)
    // {
    //     $user = new User();

    //     $form = $this->createFormBuilder($user)
    //         ->add('firstname', TextType::class, ['label' => 'Nome Cognome'])
    //         ->add('birth', DateType::class, [
    //             'label' => 'Data di nascita',
    //             'years' => range(date('Y'), 1900)
    //         ])
    //         ->add('email', EmailType::class, ['label' => 'Email'])
    //         ->add('job', ChoiceType::class, [
    //             'label' => 'Professione',
    //             'choices' => [
    //                 'Studente (fino a scuole superiori)' => User::SUPERIORI,
    //                 'Studente universitario'             => User::UNIVERSITA,
    //                 'Lavoratore'                         => User::LAVORATORE
    //             ]
    //         ])

    //         ->add('street', TextType::class, [
    //             'label'  => 'Indirizzo',
    //             'mapped' => false
    //         ])
    //         ->add('city', TextType::class, [
    //             'label'  => 'Città',
    //             'mapped' => false
    //         ])
    //         ->add('privacy', CheckboxType::class, [
    //             'mapped' => false,
    //             'required' => true,
    //             'label' => 'Ho letto e acconsento al trattamento dei miei dati // FIXME'
    //         ])
    //         ->getForm();

    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $address_info = [
    //             'street' => $form->get('street')->getData(),
    //             'city' => $form->get('city')->getData()
    //         ];

    //         $user->setAddress($address_info);
    //         $user->setCreatedAt(new \Datetime());

    //         $em = $this->getDoctrine()->getManager();
    //         $em->persist($user);
    //         $em->flush();

    //         return $this->paymentAction($user);
    //         /* return $this->redirectToRoute('payment'); */
    //     }

    //     return array(
    //         'form' => $form->createView()
    //     );
    // }

    private function paymentAction(User $user)
    {
        $gatewayName = "paypal_express_checkout";
        $storage = $this->get('payum')->getStorage('AppBundle\Entity\Payment');

        $job = $user->getJobInt();

        switch ($user->getJobInt()) {
        case User::SUPERIORI:
            $amount = 100;
            break;
        case User::UNIVERSITA:
            $amount = 1000;
            break;
        default:
            $amount = 2000;
            break;
        }

        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount($amount);
        $payment->setDescription('A description');
        $payment->setClientEmail($user->getEmail());
        $payment->setLoppureUser($user);

        $storage->update($payment);

        $captureToken = $this->get('payum')->getTokenFactory()->createCaptureToken(
            $gatewayName,
            $payment,
            'thanks'
        );

        return $this->redirect($captureToken->getTargetUrl());
    }

    /**
     * @Route("/thanks", name="thanks")
     * @Template("default/thanks.html.twig")
     */
    public function thanksAction(Request $request)
    {
        try {
            $token = $this->get('payum')->getHttpRequestVerifier()->verify($request);
            $gateway = $this->get('payum')->getGateway($token->getGatewayname());
        } catch (\Exception $e) {
            $this->addFlash(
                'error',
                'Si è verificato un errore durante la transazione'
            );
            return $this->redirectToRoute('register');
        }

        // remainder: invalidate with `$this->get('payum')->getHttpRequestVerifier()->invalidate($token)`
        /* $this->get('payum')->getHttpRequestVerifier()->invalidate($token); */

        $gateway->execute($status = new GetHumanStatus($token));
        $payment = $status->getFirstModel();

        // get user
        $user = $payment->getLoppureUser();
        $em = $this->getDoctrine()->getManager();

        // if the payment was unseccessful...
        /* dump($payment); */
        /* die(); */
        if ($status->getValue() == "failed" || !$payment->getDetails()['ACK'] == 'Success') {
            // delete user:
            $em->remove($payment);
            $em->remove($user);
            $em->flush();
            $this->addFlash(
                'error',
                'Il pagamento non è andato a buon fine!'
            );
            return $this->redirectToRoute('register');
        }

        // save the user!
        $user->setHasPayed(true);
        $user->setUpdatedAt(new \Datetime());

        $em->flush();

        // send email!
        $this->sendEmail($user);

        return array(
            'status' => $status->getValue(),
            'payment' => [
                'total_amount' => $payment->getTotalAmount(),
                'currency_code' => $payment->getCurrencyCode(),
                'details' => $payment->getDetails(),
                'payment' => $payment
            ]
        );
    }

    private function sendCode(User $user)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject("L'oppure Italia : codice di verifica email")
            ->setFrom('info@oppure.it')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'Email/verification-code.html.twig',
                    ['user' => $user]
                ),
                'text/html'
            )
            ->addPart(
                $this->renderView(
                    'Email/verification-code.txt.twig',
                    ['user' => $user]
                ),
                'text/html'
            )
            ;

        $this->get('mailer')->send($message);
    }

    private function sendEmail($user)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Hey oh! Let\' go!')
            ->setFrom('info@oppure.it')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'Email/registration.html.twig',
                    ['user' => $user]
                ),
                'text/html'
            )
            ->addPart(
                $this->renderView(
                    'Email/registration.txt.twig',
                    ['user' => $user]
                ),
                'text/html'
            )
            ;

        $this->get('mailer')->send($message);
    }
}
