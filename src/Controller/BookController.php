<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class BookController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(BookRepository $bookRepository): Response
    {
        return $this->render('book/index.html.twig', [
            'books' => $bookRepository->findAll(),
        ]);
    }

    #[Route('/book/{slug}', name: 'app_book_show')]
    public function show(string $slug, BookRepository $bookRepository): Response
    {
        $book = $bookRepository->findOneBy(['slug' => $slug]);

        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/new', name: 'app_book_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $book = new Book();

        $form = $this->createForm(BookType::class, $book);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $book->setAddedBy($this->getUser());
            $entityManager->persist($book);
            $entityManager->flush();
            return $this->redirectToRoute('app_book_show', ['slug' => $book->getSlug()]);
        }

        return $this->render('book/new.html.twig', [    
            'form' => $form,
        ]);
    }

    #[Route('/edit/{slug}', name: 'app_book_edit')]
    public function edit(Request $request, string $slug, BookRepository $bookRepository, EntityManagerInterface $entityManager): Response
    {
        $book = $bookRepository->findOneBy(['slug' => $slug]);
        $form = $this->createForm(BookType::class, $book);        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_book_show', ['slug' => $book->getSlug()]);
        }

        return $this    ->render('book/edit.html.twig', [
            'form' => $form,
            'book' => $book
        ]);
    }

    #[Route('/delete/{slug}', name: 'app_book_delete')]
    public function delete(Request $request, string $slug, BookRepository $bookRepository, EntityManagerInterface $entityManager): Response
    {
        $book = $bookRepository->findOneBy(['slug' => $slug]);

        if ($this->isCsrfTokenValid('delete'.$book->getSlug(), $request->request->get('_token'))) {
            $entityManager->remove($book);
            $entityManager->flush();
        }

        $this->addFlash('danger', 'Le livre n\'a pas été supprimé');
        return $this->redirectToRoute('app_book_show', ['slug' => $book->getSlug()], Response::HTTP_SEE_OTHER);
    }
}
