<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use AppBundle\Entity\Advert;
use AppBundle\Entity\User;
use AppBundle\Form\AdvertType;
use AppBundle\Form\LoginType;
use AppBundle\Form\RegisterType;


class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Advert');
        $adverts = $repository->findBy([], ['createdAt' => 'desc']);

        $form = $this->createForm(AdvertType::class);

        return $this->render('default/index.html.twig',array(
            'adverts' => $adverts,
            'form' => $form->createView()
        ));
    }

    public function myAdvertsAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Advert');

        $form = $this->createForm(AdvertType::class);

        $adverts = $repository->findBy([
            'author' => $this->getUser()->getUsername()
        ], [
            'createdAt' => 'desc',
        ]);

        return $this->render('default/adverts.html.twig',array(
            'adverts' => $adverts,
            'form' => $form->createView()
        ));
    }


    public function loginAction(Request $request)
    {
        $form = $this->createForm(LoginType::class);

        if ($request->isMethod('POST')) {

        }

        return $this->render('default/login.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function registerAction(Request $request)
    {
        $form = $this->createForm(RegisterType::class);

        $saved = false;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $user = new User();

                $encoder = $this->container->get('security.password_encoder');

                $user->setUsername($form->getData()->getUsername());
                $user->setPassword($encoder->encodePassword($user, $form->getData()->getPassword()));

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $saved = true;
            }
        }

        return $this->render('default/register.html.twig', [
            'form' => $form->createView(),
            'saved' => $saved
        ]);
    }

    public function createAdvertAction(Request $request)
    {
        $advert = new Advert();

        $form = $this->createForm(AdvertType::class, $advert);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $time = new \DateTime();
            $advert->setAuthor($this->getUser()->getUsername());
            $advert->setCreatedAt($time);

            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();

            return $this->json(['success' => true]);
        }

        $error = [];

        foreach ($form->getErrors(true) as $singleError) {
            $errorMessage = $singleError->getMessage();
        }

        return $this->json(['success' => false, 'message' => $errorMessage]);
    }
}
