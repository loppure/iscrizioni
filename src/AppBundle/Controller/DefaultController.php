<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Payum\Core\Request\GetHumanStatus;
use AppBundle\Entity\User;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="register")
     * @Template("default/register.html.twig")
     */
    public function registerAction(Request $request)
    {
        $user = new User();

        $form = $this->createFormBuilder($user)
            ->add('firstname', TextType::class, ['label' => 'Nome Cognome'])
            ->add('birth', DateType::class, [
                'label' => 'Data di nascita',
                'years' => range(date('Y'), 1900)
            ])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('job', ChoiceType::class, [
                'label' => 'Professione',
                'choices' => [
                    'Studente (fino a scuole superiori)' => User::SUPERIORI,
                    'Studente universitario'             => User::UNIVERSITA,
                    'Lavoratore'                         => User::LAVORATORE
                ]
            ])

            ->add('street', TextType::class, [
                'label'  => 'Indirizzo',
                'mapped' => false
            ])
            ->add('city', TextType::class, [
                'label'  => 'Città',
                'mapped' => false
            ])
            ->add('privacy', CheckboxType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Ho letto e acconsento al trattamento dei miei dati // FIXME'
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address_info = [
                'street' => $form->get('street')->getData(),
                'city' => $form->get('city')->getData()
            ];

            $user->setAddress($address_info);
            $user->setCreatedAt(new \Datetime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->paymentAction($user);
            /* return $this->redirectToRoute('payment'); */
        }

        return array(
            'form' => $form->createView()
        );
    }

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
