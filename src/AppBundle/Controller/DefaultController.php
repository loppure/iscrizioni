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

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template("default/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        return array();
    }

    /**
     * @Route("/register", name="register")
     * @Template("default/register.html.twig")
     */
    public function registerAction(Request $request)
    {
        $user = new User();

        $form = $this->createFormBuilder($user)
            ->add('firstname', TextType::class, ['label' => 'Nome'])
            ->add('lastname', TextType::class, ['label' => 'Cognome'])
            ->add('birth', DateType::class, [
                'label' => 'Data di nascita',
                'years' => range(1950, date('Y'))
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
            ->add('province', TextType::class, [
                'label'  => 'Provincia',
                'mapped' => false
            ])
            ->add('city', TextType::class, [
                'label'  => 'Città',
                'mapped' => false
            ])
            ->add('street', TextType::class, [
                'label'  => 'Via (e numero civico)',
                'mapped' => false
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address_info = [
                'province' => $form->get('province')->getData(),
                'city' => $form->get('city')->getData(),
                'street' => $form->get('street')->getData()
            ];

            $user->setAddress($address_info);

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
            $amount = 5;
            break;
        case User::UNIVERSITA:
            $amount = 10;
        default:
            $amount = 20;
            break;
        }

        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount($amount); //
        $payment->setDescription('A description'); //
        $payment->setClientEmail($user->getEmail()); //

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
        $token = $this->get('payum')->getHttpRequestVerifier()->verify($request);
        $gateway = $this->get('payum')->getGateway($token->getGatewayname());

        // remainder: invalidate with `$this->get('payum')->getHttpRequestVerifier()->invalidate($token)`

        $gateway->execute($status = new GetHumanStatus($token));
        $payment = $status->getFirstModel();

        return array(
            'status' => $status->getValue(),
            'payment' => [
                'total_amount' => $payment->getTotalAmount(),
                'currency_code' => $payment->getCurrencyCode(),
                'details' => $payment->getDetails()
            ]
        );
    }
}
