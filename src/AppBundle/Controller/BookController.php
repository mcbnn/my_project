<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Book;
use AppBundle\Form\BookForm;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class BookController
 *
 * @package AppBundle\Controller
 */
class BookController extends Controller
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
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/show_book/{id}", name="show_book", requirements={"id"="\d+"})
     */
    public function showAction($id)
    {
        $obj = $this->getEntity($id);
        if (!$obj) {
            throw $this->createNotFoundException('Книга не найдена');
        }

        return $this->render(
            'AppBundle:Book:show.html.twig',
            [
                'book' => $obj,
            ]
        );

    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/del_book/{id}", name="del_book", requirements={"id"="\d+"})
     */
    public function delAction($id)
    {
        $obj = $this->getEntity($id);
        if (!$obj) {
            throw $this->createNotFoundException('Книга не найдена');
        }
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getEntityManager();
        $authors = $em->getRepository('AppBundle\Entity\Author')->findBy(
            ['book_id' => $id]
        );
        if ($authors) {
            foreach ($authors as $author) {
                /** @var \AppBundle\Entity\Author $author */
                $author->setBookId(null);
            }
        }
        if ($obj->getFoto()) {
            $foto = $this->getParameter('brochures_directory')
                .'/'
                .$obj->getFoto();
            unlink($foto);
        }
        $em->remove($obj);
        $em->flush();

        return $this->redirectToRoute('books');
    }

    /**
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/books/{page}", name="books", requirements={"page"="\d+"})
     */
    public function getListAction($page = 1)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository('AppBundle\Entity\Book')->findAll();
        $paginator = $this->get('knp_paginator');
        $items = $paginator->paginate(
            $query,
            $page,
            5
        );

        return $this->render(
            'AppBundle:Book:books.html.twig',
            [
                'pagination' => $items,
            ]
        );
    }

    /**
     * @param Request $request
     * @param         $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/edit_book/{id}", name="edit_book", requirements={"id"="\d+"})
     */
    public function editAction(Request $request, $id)
    {

        $obj = $this->getEntity($id);
        if (!$obj) {
            throw $this->createNotFoundException('Книга не найдена');
        }
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getEntityManager();
        $form = $this->createForm(BookForm::class, $obj);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $book = $form->getData();
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $book->getAttachment();
            if ($file) {
                $fileName = md5(uniqid()).'.'.$file->guessExtension();;
                $file->move(
                    $this->getParameter('brochures_directory'),
                    $fileName
                );
                $book->setFoto($fileName);
            }
            foreach ($form->getData()->getAuthors() as $author) {
                /** @var \AppBundle\Entity\Author $author */
                $author->setBookId($book);
            }
            $em->flush();

            return $this->redirectToRoute('books');
        }

        return $this->render(
            'AppBundle:Book:edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/add_book", name="add_book")
     */
    public function addAction(Request $request)
    {
        $form = $this->createForm(BookForm::class);
        $form->handleRequest($request);
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getEntityManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $book = $form->getData();
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $book->getAttachment();
            if ($file) {
                $fileName = md5(uniqid()).'.'.$file->guessExtension();;
                $file->move(
                    $this->getParameter('brochures_directory'),
                    $fileName
                );
                $book->setFoto($fileName);
            }
            $em->persist($book);
            foreach ($form->getData()->getAuthors() as $author) {
                /** @var \AppBundle\Entity\Author $author */
                $author->setBookId($book);
            }
            $em->flush();

            return $this->redirectToRoute('books');
        }

        return $this->render(
            'AppBundle:Book:add.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @param $id
     *
     * @return bool|null|object
     */
    protected function getEntity($id)
    {
        try {
            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $this->getEntityManager();
            return $em->getRepository(Book::class)->find(
                    $id
                );
        } catch (Exception $e) {
            return false;
        }
    }

}
