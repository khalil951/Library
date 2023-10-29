<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Author;
use App\Form\BookType;
use App\Repository\BookRepository;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class BookController extends AbstractController
{
    #[Route('/', name: 'app_book')]
    public function index(BookRepository $bookRepository): Response
    {
        $publishedBooks = $bookRepository->findBy(['published' => true]);
        $unpublishedBooks = $bookRepository->findBy(['published' => false]);

        return $this->render('book/index.html.twig', [
            'books' => $publishedBooks,
            'publishedBooksNbr' => count($publishedBooks),
            'unpublishedBooksNbr' => count($unpublishedBooks),
        ]);
    }

    #[Route('/new_book', name: 'new_book', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $author = $book->getAuthor();
            $author->setNbBooks($author->getNbBooks() + 1);


            $entityManager->persist($book);
            $entityManager->persist($author);
            $entityManager->flush();

            return $this->redirectToRoute('app_book');
        }

        return $this->renderForm('book/add.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{ref}/edit', name: 'edit_book', methods: ['GET', 'POST'])]
    public function edit(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_book');
        }

        return $this->renderForm('book/edit.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/deleteNoBooksAuthors', name: 'delete_nobooksauthors', methods: ['GET', 'POST'])]
    public function deleteNoBooksAuthors(EntityManagerInterface $entityManager)
    {
        $authorRepository = $entityManager->getRepository(Author::class);
        $authorsWithNoBooks = $authorRepository->findBy(['nb_books' => 0]);

        foreach ($authorsWithNoBooks as $author) {
            $entityManager->remove($author);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_book');
    }

    #[Route('/delete/{ref}', name: 'delete_book')]
    public function delete(Book $book, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($book);
        $entityManager->flush();

        return $this->redirectToRoute('app_book');
    }

    #[Route('/{ref}', name: 'show_book', methods: ['GET'])]
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

}
