<?php

class Pagination
{

    /**
     * @var int
     */
    private $recordCount;
    private $recordsPerPage;
    private $totalPages;

    /**
     * @var array
     */
    private $records;

    private $view;

    public function __construct($view, $recordsPerPage)
    {
        $this->view = $view;
        $this->recordsPerPage = $recordsPerPage;
    }

    public function setRecords(array $records): void
    {
        $this->records = $records;
        $this->recordCount = count($records);
        $this->totalPages = round( $this->recordCount / $this->recordsPerPage, 0, PHP_ROUND_HALF_UP);
    }

    public function getRecords(int $page): array
    {
        return array_splice($this->records, ($page-1) * $this->recordsPerPage, $this->recordsPerPage);
    }

    public function view()
    {
        return $this->view;
    }

    public function totalPages()
    {
        return $this->totalPages;
    }

}