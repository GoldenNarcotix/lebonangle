<?php


namespace App\Controller;


use App\Entity\AdminUser;
use App\Form\AdminUserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminUserController extends AbstractController
{
    /**
     * @Route("admin/add_user", name="app_add_user")
     */
    public function new(Request $request)
    {
        $adminUser = new AdminUser();

        $loginForm = $this->createForm(AdminUserType::class, $adminUser);

        $loginForm->handleRequest($request);
        if($loginForm->isSubmitted() && $loginForm->isValid())
        {
            $adminUser = $loginForm->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($adminUser);
            $entityManager->flush();

            return $this->redirectToRoute('app_show_users');
        }

        return $this->render('Admin/Form/user_form.html.twig', [
            'loginForm' => $loginForm->createView(),
        ]);
    }

    /**
     * @Route("/admin/ShowUsers", name="app_show_users")
     */
    public function showUsers()
    {
        $adminUsers = $this->getDoctrine()
            ->getRepository(AdminUser::class)
            ->findAll();

        return $this->render('Admin/users.html.twig',
        ['adminusers' => $adminUsers,]);
    }

    /**
     * @Route("/admin/ShowUser/{id}", name="app_show_user")
     */
    public function showUser($id)
    {
        $adminUser = $this->getDoctrine()
            ->getRepository(AdminUser::class)
            ->findOneBy(['id' => $id]);

        return $this->render('Admin/user_details.html.twig',[
            'adminUser' => $adminUser,
        ]);
    }

    /**
     * @Route("/admin/editUser/{id}", name="app_edit_user")
     */
    public function edit(AdminUser $adminUser, Request $request)
    {
        $userEditForm = $this->createForm(AdminUserType::class, $adminUser);
        $userEditForm->handleRequest($request);

        if($userEditForm->isSubmitted() && $userEditForm->isValid())
        {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userEditForm->getData());
            $entityManager->flush();

            return $this->redirectToRoute('app_show_users');
        }

        return $this->render('Admin/Form/edit_user_form.html.twig',[
            'userEditForm' => $userEditForm->createView(),
        ]);

    }

    /**
     * @Route("/admin/deleteUser/{id}", name="app_delete_user")
     */
    public function delete(AdminUser $adminUser)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($adminUser);
        $entityManager->flush();
        return $this->redirectToRoute('app_show_users');
    }

}