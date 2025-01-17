<?php

namespace App;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class GoogleBook
{
  private readonly string $endpoint;

  public function __construct(
    private readonly HttpClientInterface $client,
  ) {
    $this->endpoint = sprintf('https://www.googleapis.com/books/v1/volumes');
  }

  public function getBooks(?string $title = null, ?string $author = null): array
  {
    if (empty($title) && empty($author)) {
      return [];
    }

    $response = $this->client->request('GET', $this->endpoint, [
      'query' => [
        'q' => sprintf('%s %s', $author, $title),
        'maxResults' => 10,
        'orderBy' => 'relevance',
      ],
    ]);

    $result = $response->toArray();

    if (empty($result['items'])) {
      return [];
    }

    $items = array_map(function ($item) {
      return [
        'id' => $item['id'],
        'title' => $item['volumeInfo']['title'],
        'description' => $item['volumeInfo']['description'],
        'image' => $item['volumeInfo']['imageLinks']['thumbnail'],
        'author' => implode(', ', $item['volumeInfo']['authors']),
        'publishedAt' => $item['volumeInfo']['publishedDate'],
        'isbn' => $item['volumeInfo']['industryIdentifiers'][0]['identifier'],
      ];
    }, $result['items']);

    return $items;
  }
}