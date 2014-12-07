<?php

namespace Anaiel\Repository;

use Doctrine\DBAL\Connection;

class PageRepository
{
    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var SectionRepository
     */
    private $sectionRepo;
    private $saveStmt;

    /**
     * Constructor
     *
     * @param Connection        $conn
     * @param SectionRepository $sectionRepo
     */
    public function __construct(Connection $conn, SectionRepository $sectionRepo)
    {
        $this->conn = $conn;
        $this->sectionRepo = $sectionRepo;
    }

    public function save($page)
    {
        if (!$this->saveStmt) {
            $this->saveStmt = $this->conn->prepare(
                'INSERT INTO anaiel.pages (id, book_id, title) VALUES(:id, :book_id, :title) ON DUPLICATE KEY UPDATE title = VALUES(title), book_id = VALUES(book_id);'
            );
        }

        $this->saveStmt->execute(array(
            'id' => isset($page['id']) ? $page['id'] : null,
            'book_id' => $page['book_id'],
            'title' => $page['title']
        ));

        $id = isset($page['id']) ? $page['id'] : $this->conn->lastInsertId();
        foreach ($page['sections'] as $section) {
            $section['page_id'] = $id;
            $this->sectionRepo->save($section);
        }
    }
}
