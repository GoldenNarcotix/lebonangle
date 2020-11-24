<?php


namespace App\Controller;


use App\Entity\Category;
use App\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("admin/add_category", name="app_add_category")
     */
    public function new(Request $request)
    {
        $category = new Category();

        $categoryForm = $this->createForm(CategoryType::class, $category);

        $categoryForm->handleRequest($request);
        if($categoryForm->isSubmitted() && $categoryForm->isValid())
        {
            $category = $categoryForm->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_show_categories');
        }

        return $this->render('Admin/Form/categories_form.html.twig', [
            'categoryForm' => $categoryForm->createView(),
        ]);
    }

    /**
     * @Route("/admin/ShowCategories", name="app_show_categories")
     */
    public function showCategories()
    {
        $categories = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();

        return $this->render('Admin/categories.html.twig',
            ['categories' => $categories,]);
    }

    /**
     * @Route("/admin/ShowCategory/{id}", name="app_show_category")
     */
    public function showCategory($id)
    {
        $category = $this->
        getDoctrine()->
        getRepository(Category::class)->
        findOneBy(['id' => $id]);

        return $this->render('Admin/Details/category_details.html.twig',[
            'category' => $category,
        ]);
    }

    /**
     * @Route("/admin/EditCategory/{id}", name="app_edit_category")
     */
    public function edit(Category $category, Request $request)
    {
        $categoryForm = $this->createForm(CategoryType::class, $category);
        $categoryForm->handleRequest($request);

        if($categoryForm->isSubmitted() && $categoryForm->isValid())
        {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($categoryForm->getData());
            $entityManager->flush();

            return $this->redirectToRoute('app_show_categories');
        }

        return $this->render('Admin/Form/categories_form.html.twig',[
            'categoryForm' => $categoryForm->createView(),
        ]);
    }

    /**
     * @Route("/admin/deleteCategory/{id}", name="app_delete_category")
     */
    public function delete(Category $category)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($category);
        $entityManager->flush();
        return $this->redirectToRoute('app_show_categories');
    }
}