<?php

namespace Anaiel\Repository;

use Doctrine\DBAL\Connection;

class SectionRepository
{
    /**
     * @var Connection
     */
    private $conn;
    private $saveStmt;

    /**
     * Constructor
     *
     * @param Connection $conn
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function save($section)
    {
        if (!$this->saveStmt) {
            $this->saveStmt = $this->conn->prepare(
                'INSERT INTO anaiel.page_sections (id, page_id, text, code) VALUES(:id, :page_id, :text, :code) ON DUPLICATE KEY UPDATE text = VALUES(text), code = VALUES(code), page_id = VALUES(page_id);'
            );
        }

        $this->saveStmt->execute(array(
            'id' => isset($section['id']) ? $section['id'] : null,
            'page_id' => $section['page_id'],
            'text' => $section['text'],
            'code' => $section['code']
        ));
    }
}
