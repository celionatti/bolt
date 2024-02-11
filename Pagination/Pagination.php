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
	protected $totalItems;
    protected $itemsPerPage;
    protected $currentPage;

    public function __construct($totalItems, $itemsPerPage, $currentPage)
    {
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = $currentPage;
    }

    public function getTotalPages()
    {
        return ceil($this->totalItems / $this->itemsPerPage);
    }

    public function getStartItem()
    {
        return ($this->currentPage - 1) * $this->itemsPerPage + 1;
    }

    public function getEndItem()
    {
        $endItem = $this->currentPage * $this->itemsPerPage;
        return ($endItem > $this->totalItems) ? $this->totalItems : $endItem;
    }

    public function hasPreviousPage()
    {
        return $this->currentPage > 1;
    }

    public function getPreviousPage()
    {
        return ($this->hasPreviousPage()) ? $this->currentPage - 1 : null;
    }

    public function hasNextPage()
    {
        return $this->currentPage < $this->getTotalPages();
    }

    public function getNextPage()
    {
        return ($this->hasNextPage()) ? $this->currentPage + 1 : null;
    }

    public function generateBootstrapDotsLinks($url)
    {
        $links = '';

        if ($this->getTotalPages() > 1) {
            $links .= '<nav aria-label="Page navigation"><ul class="pagination">';
            
            if ($this->hasPreviousPage()) {
                $links .= '<li class="page-item"><a class="page-link" href="' . $url . '&page=' . $this->getPreviousPage() . '">Previous</a></li>';
            }

            $maxLinks = 5; // Maximum links to show before and after the current page
            $halfMaxLinks = floor($maxLinks / 2);
            $startPage = max(1, $this->currentPage - $halfMaxLinks);
            $endPage = min($this->getTotalPages(), $startPage + $maxLinks - 1);

            if ($startPage > 1) {
                $links .= '<li class="page-item"><a class="page-link" href="' . $url . '&page=1">1</a></li>';
                if ($startPage > 2) {
                    $links .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            for ($i = $startPage; $i <= $endPage; $i++) {
                $activeClass = ($i == $this->currentPage) ? 'active' : '';
                $links .= '<li class="page-item ' . $activeClass . '"><a class="page-link" href="' . $url . '&page=' . $i . '">' . $i . '</a></li>';
            }

            if ($endPage < $this->getTotalPages()) {
                if ($endPage < $this->getTotalPages() - 1) {
                    $links .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                $links .= '<li class="page-item"><a class="page-link" href="' . $url . '&page=' . $this->getTotalPages() . '">' . $this->getTotalPages() . '</a></li>';
            }

            if ($this->hasNextPage()) {
                $links .= '<li class="page-item"><a class="page-link" href="' . $url . '&page=' . $this->getNextPage() . '">Next</a></li>';
            }

            $links .= '</ul></nav>';
        }

        return $links;
    }

    public function generateBootstrapDefLinks($url)
    {
        $links = '';

        if ($this->getTotalPages() > 1) {
            $links .= '<ul class="pagination justify-content-center">';

            if ($this->hasPreviousPage()) {
                $links .= '<li class="page-item"><a href="' . $url . '&page=' . $this->getPreviousPage() . '" class="page-link">&laquo;</a></li>';
            }

            $maxLinks = 5; // Maximum links to show before and after the current page
            $halfMaxLinks = floor($maxLinks / 2);
            $startPage = max(1, $this->currentPage - $halfMaxLinks);
            $endPage = min($this->getTotalPages(), $startPage + $maxLinks - 1);

            for ($i = $startPage; $i <= $endPage; $i++) {
                $activeClass = ($i == $this->currentPage) ? 'active' : '';
                $links .= '<li class="page-item ' . $activeClass . '"><a href="' . $url . '&page=' . $i . '" class="page-link">' . $i . '</a></li>';
            }

            if ($this->hasNextPage()) {
                $links .= '<li class="page-item"><a href="' . $url . '&page=' . $this->getNextPage() . '" class="page-link">&raquo;</a></li>';
            }

            $links .= '</ul>';
        }

        return $links;
    }
}