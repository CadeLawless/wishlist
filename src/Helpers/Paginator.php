<?php

namespace Helpers;

use Core\Model;
use mysqli;

class Paginator extends Model {
    private string $baseQuery;
    private array $params;
    private int $limit = 10;
    private int $page = 1;
    private int $totalRows = 0;
    private int $totalPages = 0;

    public function __construct(string $baseQuery, array $params = []) {
        $this->baseQuery = $baseQuery;
        $this->params = $params;
    }

    public function setLimit(int $limit): self {
        $this->limit = $limit;
        return $this;
    }

    public function setPage(int $page): self {
        $this->page = max(1, $page);
        return $this;
    }

    public function getData(): array {
        $this->calculateTotals();
        $offset = ($this->page - 1) * $this->limit;
        $sql = $this->baseQuery . " LIMIT ? OFFSET ?";

        return $this->select(query: $sql, values: array_merge($this->params, [$this->limit, $offset]));
    }

    private function calculateTotals(): void {
        $countQuery = "SELECT COUNT(*) FROM ({$this->baseQuery}) AS sub";
        $this->totalRows = (int) $this->select(query: $countQuery, values: $this->params, singleResult: true)["sub"];
        $this->totalPages = (int) ceil($this->totalRows / $this->limit);
    }

    public function getPaginationInfo(): array {
        return [
            'current' => $this->page,
            'totalPages' => $this->totalPages,
            'totalRows' => $this->totalRows,
            'start' => ($this->page - 1) * $this->limit + 1,
            'end' => min($this->page * $this->limit, $this->totalRows)
        ];
    }
}

?>