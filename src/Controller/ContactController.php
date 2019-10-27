<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Form\FormErrorsSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Request\ContactRequest;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="contact")
     */
    public function index(Request $request, \Swift_Mailer $mailer, FormErrorsSerializer $serializer)
    {
        $form = $this->createForm(ContactType::class);
		$contactRequest = new ContactRequest();
		$form->setData($contactRequest);
		
		if (\in_array($request->getMethod(), ['POST', 'PUT'], true)) {
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				$contactFormData = $form->getData();

				$message = (new \Swift_Message('You Got Mail from Symfony 4!'))
					->setFrom($contactFormData['fromEmail'])
					->setTo('email@domain.nettt')
					->setBody(
						$contactFormData['message'],
						'text/plain'
					);

				$mailer->send($message);

				$this->addFlash('success', 'Message was send');

				return $this->redirectToRoute('contact');
			}
		}
		$errors = $serializer->serialize($form);
        return $this->render('contact/index.html.twig', [
            'email_form' => $form->createView(),
			'errors' => $errors,
        ]);
    }
}
