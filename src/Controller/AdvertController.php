<?php


namespace App\Controller;


use App\Entity\Advert;
use App\Entity\Category;
use App\Form\AdvertType;
use Doctrine\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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
    public function handle(Advert $advert, string $status, MailerInterface $mailer)
    {
        //Récuperation du workflow
        $advertPublishingWorkflow = $this->advertPublishingWorkflow->get($advert);

        if($advertPublishingWorkflow->can($advert, $status))
        {
            $advertPublishingWorkflow->apply($advert, $status);
            $this->getDoctrine()->getManager()->flush();
            if($status === "publish")
            {
                $advert->setPublishedAt(new \DateTime('now'));
                $this->getDoctrine()->getManager()->flush();


                $email = (new TemplatedEmail())
                    ->from('lebonangle@admin.fr')
                    ->to($advert->getEmail())
                    ->subject('Merci d\'avoir publié votre annonce !')
                    ->htmlTemplate('Email/published.html.twig')
                    ->context([
                        'advert' => $advert
                    ]);

                $mailer->send($email);
            }


        }

        return $this->redirectToRoute('app_show_adverts');
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
    public function showAdverts(PaginatorInterface $paginator, Request $request)
    {
        $adverts = $this->getDoctrine()
            ->getRepository(Advert::class)
            ->findAll();

        $advertsPaginate = $paginator->paginate($adverts, $request->query->getInt('page', 1), 2);

        return $this->render('adverts.html.twig', [
            'adverts' => $adverts,
            'advertsPaginate' => $advertsPaginate,
        ]);
    }

    /**
     * @Route("/admin/ShowAdvert/{id}", name="app_show_advert")
     */
    public function showAdvert($id)
    {
        $advert = $this->
        getDoctrine()->
        getRepository(Advert::class)->
        findOneBy(['id' => $id]);

        return $this->render('Admin/Details/advert_details.html.twig',[
            'advert' => $advert,
        ]);
    }


}