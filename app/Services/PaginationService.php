<?php

namespace App\Services;

class PaginationService
{
    private int $itemsPerPage;
    private int $currentPage;
    private int $totalItems;
    private int $totalPages;
    private int $offset;

    public function __construct(int $itemsPerPage = 12)
    {
        $this->itemsPerPage = $itemsPerPage;
    }

    public function paginate(array $items, int $page = 1): array
    {
        $this->currentPage = max(1, $page);
        $this->totalItems = count($items);
        $this->totalPages = max(1, ceil($this->totalItems / $this->itemsPerPage));
        $this->offset = ($this->currentPage - 1) * $this->itemsPerPage;

        // Ensure current page doesn't exceed total pages
        if ($this->currentPage > $this->totalPages) {
            $this->currentPage = $this->totalPages;
            $this->offset = ($this->currentPage - 1) * $this->itemsPerPage;
        }

        return array_slice($items, $this->offset, $this->itemsPerPage);
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function getNextPage(): ?int
    {
        return $this->hasNextPage() ? $this->currentPage + 1 : null;
    }

    public function getPreviousPage(): ?int
    {
        return $this->hasPreviousPage() ? $this->currentPage - 1 : null;
    }

    public function getPageNumbers(int $maxVisible = 5): array
    {
        $pages = [];
        $start = max(1, $this->currentPage - floor($maxVisible / 2));
        $end = min($this->totalPages, $start + $maxVisible - 1);

        // Adjust start if we're near the end
        if ($end - $start + 1 < $maxVisible) {
            $start = max(1, $end - $maxVisible + 1);
        }

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        return $pages;
    }

    public function buildControls(string $baseUrl, array $queryParams = []): string
    {
        $html = '<div class="pagination-controls">';
        
        // First page
        if ($this->hasPreviousPage()) {
            $firstUrl = $this->buildUrl($baseUrl, 1, $queryParams);
            $html .= '<a href="' . $firstUrl . '" class="paginate-arrow paginate-first">First</a>';
        } else {
            $html .= '<span class="paginate-arrow paginate-first disabled">First</span>';
        }

        // Previous page
        if ($this->hasPreviousPage()) {
            $prevUrl = $this->buildUrl($baseUrl, $this->getPreviousPage(), $queryParams);
            $html .= '<a href="' . $prevUrl . '" class="paginate-arrow paginate-previous">Previous</a>';
        } else {
            $html .= '<span class="paginate-arrow paginate-previous disabled">Previous</span>';
        }

        // Page numbers
        $pageNumbers = $this->getPageNumbers();
        foreach ($pageNumbers as $pageNum) {
            if ($pageNum == $this->currentPage) {
                $html .= '<span class="page-number current">' . $pageNum . '</span>';
            } else {
                $pageUrl = $this->buildUrl($baseUrl, $pageNum, $queryParams);
                $html .= '<a href="' . $pageUrl . '" class="page-number">' . $pageNum . '</a>';
            }
        }

        // Next page
        if ($this->hasNextPage()) {
            $nextUrl = $this->buildUrl($baseUrl, $this->getNextPage(), $queryParams);
            $html .= '<a href="' . $nextUrl . '" class="paginate-arrow paginate-next">Next</a>';
        } else {
            $html .= '<span class="paginate-arrow paginate-next disabled">Next</span>';
        }

        // Last page
        if ($this->hasNextPage()) {
            $lastUrl = $this->buildUrl($baseUrl, $this->totalPages, $queryParams);
            $html .= '<a href="' . $lastUrl . '" class="paginate-arrow paginate-last">Last</a>';
        } else {
            $html .= '<span class="paginate-arrow paginate-last disabled">Last</span>';
        }

        $html .= '</div>';
        return $html;
    }

    private function buildUrl(string $baseUrl, int $page, array $queryParams = []): string
    {
        $queryParams['pageno'] = $page;
        $queryString = http_build_query($queryParams);
        return $baseUrl . ($queryString ? '?' . $queryString : '');
    }

    public function getShowingText(): string
    {
        $start = $this->offset + 1;
        $end = min($this->offset + $this->itemsPerPage, $this->totalItems);
        return "Showing {$start}-{$end} of {$this->totalItems} items";
    }
}
