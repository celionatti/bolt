<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - Pagination Class ============
 * ====================================
 */

namespace celionatti\Bolt\Pagination;

use celionatti\Bolt\Config;
use celionatti\Bolt\BoltException\BoltException;

class Pagination
{
    private int $totalItems;
    private int $itemsPerPage;
    private int $currentPage;

    /**
     * Pagination constructor.
     *
     * @param int $totalItems
     * @param int $itemsPerPage
     * @param int $currentPage
     */
    public function __construct(int $totalItems, int $itemsPerPage, int $currentPage)
    {
        if ($totalItems < 0 || $itemsPerPage <= 0 || $currentPage <= 0) {
            throw new \InvalidArgumentException('Invalid pagination parameters.');
        }

        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = $currentPage;
    }

    /**
     * Get total number of pages.
     *
     * @return int
     */
    public function getTotalPages(): int
    {
        return (int)ceil($this->totalItems / $this->itemsPerPage);
    }

    /**
     * Get the start item number for the current page.
     *
     * @return int
     */
    public function getStartItem(): int
    {
        return ($this->currentPage - 1) * $this->itemsPerPage + 1;
    }

    /**
     * Get the end item number for the current page.
     *
     * @return int
     */
    public function getEndItem(): int
    {
        $endItem = $this->currentPage * $this->itemsPerPage;
        return ($endItem > $this->totalItems) ? $this->totalItems : $endItem;
    }

    /**
     * Check if there is a previous page.
     *
     * @return bool
     */
    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    /**
     * Get the previous page number.
     *
     * @return ?int
     */
    public function getPreviousPage(): ?int
    {
        return $this->hasPreviousPage() ? $this->currentPage - 1 : null;
    }

    /**
     * Check if there is a next page.
     *
     * @return bool
     */
    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->getTotalPages();
    }

    /**
     * Get the next page number.
     *
     * @return ?int
     */
    public function getNextPage(): ?int
    {
        return $this->hasNextPage() ? $this->currentPage + 1 : null;
    }

    /**
     * Generate pagination links with ellipses for Bootstrap.
     *
     * @param string $url
     * @return string
     */
    public function generateBootstrapDotsLinks(string $url): string
    {
        $links = '';

        if ($this->getTotalPages() > 1) {
            $links .= '<nav aria-label="Page navigation"><ul class="pagination">';

            if ($this->hasPreviousPage()) {
                $prevUrl = $url . ((strpos($url, '?') !== false) ? '&' : '?') . 'page=' . $this->getPreviousPage();
                $links .= '<li class="page-item"><a class="page-link" href="' . $prevUrl . '">Prev</a></li>';
            }

            $maxLinks = 5; // Maximum links to show before and after the current page
            $halfMaxLinks = floor($maxLinks / 2);
            $startPage = max(1, $this->currentPage - $halfMaxLinks);
            $endPage = min($this->getTotalPages(), $startPage + $maxLinks - 1);

            if ($startPage > 1) {
                $links .= '<li class="page-item"><a class="page-link" href="' . $url . ((strpos($url, '?') !== false) ? '&' : '?') . 'page=1">1</a></li>';
                if ($startPage > 2) {
                    $links .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            for ($i = $startPage; $i <= $endPage; $i++) {
                $activeClass = ($i == $this->currentPage) ? 'active' : '';
                $pageUrl = $url . ((strpos($url, '?') !== false) ? '&' : '?') . 'page=' . $i;
                $links .= '<li class="page-item ' . $activeClass . '"><a class="page-link" href="' . $pageUrl . '">' . $i . '</a></li>';
            }

            if ($endPage < $this->getTotalPages()) {
                if ($endPage < $this->getTotalPages() - 1) {
                    $links .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                $links .= '<li class="page-item"><a class="page-link" href="' . $url . ((strpos($url, '?') !== false) ? '&' : '?') . 'page=' . $this->getTotalPages() . '">' . $this->getTotalPages() . '</a></li>';
            }

            if ($this->hasNextPage()) {
                $nextUrl = $url . ((strpos($url, '?') !== false) ? '&' : '?') . 'page=' . $this->getNextPage();
                $links .= '<li class="page-item"><a class="page-link" href="' . $nextUrl . '">Next</a></li>';
            }

            $links .= '</ul></nav>';
        }

        return $links;
    }

    /**
     * Generate default Bootstrap pagination links.
     *
     * @param string $url
     * @return string
     */
    public function generateBootstrapDefLinks(string $url): string
    {
        $links = '';

        if ($this->getTotalPages() > 1) {
            $links .= '<ul class="pagination justify-content-center">';

            if ($this->hasPreviousPage()) {
                $prevUrl = $url . ((strpos($url, '?') !== false) ? '&' : '?') . 'page=' . $this->getPreviousPage();
                $links .= '<li class="page-item"><a href="' . $prevUrl . '" class="page-link">&laquo;</a></li>';
            }

            $maxLinks = 5; // Maximum links to show before and after the current page
            $halfMaxLinks = floor($maxLinks / 2);
            $startPage = max(1, $this->currentPage - $halfMaxLinks);
            $endPage = min($this->getTotalPages(), $startPage + $maxLinks - 1);

            for ($i = $startPage; $i <= $endPage; $i++) {
                $activeClass = ($i == $this->currentPage) ? 'active' : '';
                $pageUrl = $url . ((strpos($url, '?') !== false) ? '&' : '?') . 'page=' . $i;
                $links .= '<li class="page-item ' . $activeClass . '"><a href="' . $pageUrl . '" class="page-link">' . $i . '</a></li>';
            }

            if ($this->hasNextPage()) {
                $nextUrl = $url . ((strpos($url, '?') !== false) ? '&' : '?') . 'page=' . $this->getNextPage();
                $links .= '<li class="page-item"><a href="' . $nextUrl . '" class="page-link">&raquo;</a></li>';
            }

            $links .= '</ul>';
        }

        return $links;
    }

    /**
     * Generate pagination links with only "Next" and "Previous" buttons for Bootstrap.
     *
     * @param string $url
     * @return string
     */
    public function generateNextPrevLinks(string $url): string
    {
        $links = '';

        if ($this->getTotalPages() > 1) {
            $links .= '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

            if ($this->hasPreviousPage()) {
                $prevUrl = $url . ((strpos($url, '?') !== false) ? '&' : '?') . 'page=' . $this->getPreviousPage();
                $links .= '<li class="page-item"><a class="page-link" href="' . $prevUrl . '">Previous</a></li>';
            }

            if ($this->hasNextPage()) {
                $nextUrl = $url . ((strpos($url, '?') !== false) ? '&' : '?') . 'page=' . $this->getNextPage();
                $links .= '<li class="page-item"><a class="page-link" href="' . $nextUrl . '">Next</a></li>';
            }

            $links .= '</ul></nav>';
        }

        return $links;
    }

    /**
     * Generate pagination links with ellipses between for Bootstrap.
     *
     * @param string $url
     * @return string
     */
    public function generateEllipsesLinks(string $url): string
    {
        $links = '';

        if ($this->getTotalPages() > 1) {
            $links .= '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

            if ($this->hasPreviousPage()) {
                $prevUrl = $url . ((strpos($url, '?') !== false) ? '&' : '?') . 'page=' . $this->getPreviousPage();
                $links .= '<li class="page-item"><a class="page-link" href="' . $prevUrl . '">Previous</a></li>';
            }

            $maxLinks = 5; // Maximum links to show before and after the current page
            $halfMaxLinks = floor($maxLinks / 2);
            $startPage = max(1, $this->currentPage - $halfMaxLinks);
            $endPage = min($this->getTotalPages(), $startPage + $maxLinks - 1);

            if ($startPage > 1) {
                $links .= '<li class="page-item"><a class="page-link" href="' . $url . ((strpos($url, '?') !== false) ? '&' : '?') . 'page=1">1</a></li>';
                if ($startPage > 2) {
                    $links .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            for ($i = $startPage; $i <= $endPage; $i++) {
                $activeClass = ($i == $this->currentPage) ? 'active' : '';
                $pageUrl = $url . ((strpos($url, '?') !== false) ? '&' : '?') . 'page=' . $i;
                $links .= '<li class="page-item ' . $activeClass . '"><a class="page-link" href="' . $pageUrl . '">' . $i . '</a></li>';
            }

            if ($endPage < $this->getTotalPages()) {
                if ($endPage < $this->getTotalPages() - 1) {
                    $links .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                $links .= '<li class="page-item"><a class="page-link" href="' . $url . ((strpos($url, '?') !== false) ? '&' : '?') . 'page=' . $this->getTotalPages() . '">' . $this->getTotalPages() . '</a></li>';
            }

            if ($this->hasNextPage()) {
                $nextUrl = $url . ((strpos($url, '?') !== false) ? '&' : '?') . 'page=' . $this->getNextPage();
                $links .= '<li class="page-item"><a class="page-link" href="' . $nextUrl . '">Next</a></li>';
            }

            $links .= '</ul></nav>';
        }

        return $links;
    }
}
