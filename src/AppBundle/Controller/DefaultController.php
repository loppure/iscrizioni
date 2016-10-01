<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
            ->add('firstname', TextType::class, ['label' => 'Nome Cognome'])
            ->add('birth', DateType::class, [
                'label' => 'Data di nascita',
                'years' => range(1900, date('Y'))
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
                'label'  => 'Via (e numero civico)',
                'mapped' => false
            ])
            ->add('city', TextType::class, [
                'label'  => 'Città',
                'mapped' => false
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address_info = [
                'street' => $form->get('street')->getData(),
                'city' => $form->get('city')->getData()
            ];

            $user->setAddress($address_info);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('thanks');
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/thanks", name="thanks")
     * @Template("default/thanks.html.twig")
     */
    public function thanksAction()
    {
        return array();
    }
}
