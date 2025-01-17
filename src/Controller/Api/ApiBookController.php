<?php

namespace App\Controller\Api;

use App\GoogleBook;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ApiBookController extends AbstractController
{
  #[Route('/api/book/search', name: 'api_book_search', methods: ['GET'])]
  public function search(GoogleBook $googleBook, Request $request): Response
  {
    $title = $request->query->get('title');
    $author = $request->query->get('author');
    
    $books = $googleBook->getBooks($title, $author);

    return $this->json([
      'books' => $books,
    ]);
  }
}