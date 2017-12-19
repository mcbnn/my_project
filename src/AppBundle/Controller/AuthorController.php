<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Author;
use AppBundle\Form\AuthorForm;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;


class AuthorController extends Controller
{

    /**
     * @var null
     */
    protected $em = null;

    /**
     * @return null
     */
    public function getEntityManager()
    {
        if ($this->em == null) {
            $this->em = $this->getDoctrine()->getEntityManager();
        }

        return $this->em;
    }

    /**
     * @Route("/edit_author/{id}", name="edit_author", requirements={"id"="\d+"})
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getEntityManager();
        $obj = $this->getEntity($id);
        if (!$obj) {
            throw $this->createNotFoundException('Автор не найден');
        }
        $form = $this->createForm(AuthorForm::class, $obj);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('authors');
        }

        return $this->render(
            'AppBundle:Author:edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );

    }

    /**
     * @Route("/del_author/{id}", name="del_author", requirements={"id"="\d+"})
     */
    public function delAction($id)
    {
        $obj = $this->getEntity($id);
        if (!$obj) {
            throw $this->createNotFoundException('Автор не найден');
        }
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($obj);
        $em->flush();

        return $this->redirectToRoute('authors');
    }

    /**
     * @Route("/authors/{page}", name="authors", requirements={"page"="\d+"})
     */
    public function getListAction($page = 1)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository('AppBundle\Entity\Author')->findAll();
        $paginator = $this->get('knp_paginator');
        $items = $paginator->paginate(
            $query,
            $page,
            5
        );

        return $this->render(
            'AppBundle:Author:authors.html.twig',
            [
                'pagination' => $items,
            ]
        );
    }

    /**
     * @Route("/add_author", name="add_author")
     */
    public function addAction(Request $request)
    {
        $form = $this->createForm(AuthorForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $author = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($author);
            $em->flush();

            return $this->redirectToRoute('authors');
        }

        return $this->render(
            'AppBundle:Author:add.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Returns an entity from its ID, or FALSE in case of error.
     *
     * @param int $id
     *
     * @return Object|boolean
     */
    protected function getEntity($id)
    {
        try {
            return $this->getEntityManager()
                ->getRepository(Author::class)
                ->find($id);
        } catch (Exception $e) {
            return false;
        }

    }

}
