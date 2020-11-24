<?php


namespace App\Controller;


use App\Entity\Advert;
use App\Entity\Category;
use App\Form\AdvertType;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\WorkflowInterface;

class AdvertController extends AbstractController
{

    private $advertPublishingWorkflow;

    public function __construct(Registry $advertPublishingWorkflow)
    {
        $this->advertPublishingWorkflow = $advertPublishingWorkflow;
    }

    /**
     * @Route("/admin/adverts/{id}/workflow/{status}", name="app_adverts_workflow")
     * @param Advert $advert
     * @param string $status
     */
    public function handle(Advert $advert, string $status): void
    {
        //Récuperation du workflow
        $advertPublishingWorkflow = $this->advertPublishingWorkflow->get($advert);


        //Changement du workflow
        $advertPublishingWorkflow->apply($advert, $status);

        //Insert en bdd
        $this->getDoctrine()->getManager()->flush();

        if($advertPublishingWorkflow->can($advert, 'publish'))
        {
            $advertPublishingWorkflow->apply($advert, 'publish');
            $this->getDoctrine()->getManager()->flush();
        }
    }

    /**
     * @Route("/advert/new/", name="app_add_adverts")
     */
    public function new(Request $request)
    {
        $advert = new Advert();

        $advertForm = $this->createForm(AdvertType::class, $advert);
        $advertForm->handleRequest($request);

        if($advertForm->isSubmitted() && $advertForm->isValid())
        {
            $advert = $advertForm->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($advert);
            $entityManager->flush();

            return $this->redirectToRoute('app_show_adverts');
        }

        return $this->render('Form/adverts_form.html.twig', [
            'advertForm' => $advertForm->createView(),
        ]);
    }

    /**
     * @Route("/adverts/show", name="app_show_adverts")
     */
    public function showAdverts()
    {
        $adverts = $this->getDoctrine()
            ->getRepository(Advert::class)
            ->findAll();

        return $this->render('adverts.html.twig',
            ['adverts' => $adverts,]);
    }


}