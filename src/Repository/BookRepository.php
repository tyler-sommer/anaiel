<?php

namespace Anaiel\Repository;

use Doctrine\DBAL\Connection;

class BookRepository
{
    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var PageRepository
     */
    private $pageRepo;
    private $saveStmt;

    /**
     * Constructor
     *
     * @param Connection     $conn
     * @param PageRepository $pageRepo
     */
    public function __construct(Connection $conn, PageRepository $pageRepo)
    {
        $this->conn = $conn;
        $this->pageRepo = $pageRepo;
    }

    /**
     * Returns all Books, Pages, and Sections
     *
     * @return array
     */
    public function findAll()
    {
        $results = $this->conn->executeQuery('SELECT
  b.title AS book_title,
  b.id AS book_id,
  p.title AS page_title,
  p.id AS page_id,
  ps.text,
  ps.code,
  ps.id
FROM anaiel.books b
  LEFT JOIN anaiel.pages p ON p.book_id = b.id
  LEFT JOIN anaiel.page_sections ps ON ps.page_id = p.id;')->fetchAll();

        $normal = array();
        foreach ($results as $section) {
            $bookId = $section['book_id'];
            $pageId = $section['page_id'];
            if (!isset($normal[$bookId])) {
                $normal[$bookId] = array(
                    'id' => $bookId,
                    'title' => $section['book_title'],
                    'pages' => array()
                );
            }

            if ($pageId && !isset($normal[$bookId]['pages'][$pageId])) {
                $normal[$bookId]['pages'][$pageId] = array(
                    'id' => $pageId,
                    'title' => $section['page_title'],
                    'sections' => array()
                );
            }

            unset($section['book_id'], $section['book_title'], $section['page_id'], $section['page_title']);

            if (isset($section['id'])) {
                $normal[$bookId]['pages'][$pageId]['sections'][] = $section;
            }
        }

        $books = array();
        foreach ($normal as $book) {
            $book['pages'] = array_values($book['pages']);

            $books[] = $book;
        }

        return $books;
    }

    public function save($book)
    {
        if (!$this->saveStmt) {
            $this->saveStmt = $this->conn->prepare(
                'INSERT INTO anaiel.books (id, title) VALUES(:id, :title) ON DUPLICATE KEY UPDATE title = VALUES(title);'
            );
        }

        $this->saveStmt->execute(array(
            'id' => isset($book['id']) ? $book['id'] : null,
            'title' => $book['title']
        ));

        $id = isset($book['id']) ? $book['id'] : $this->conn->lastInsertId();
        foreach ($book['pages'] as $page) {
            $page['book_id'] = $id;
            $this->pageRepo->save($page);
        }
    }
}
