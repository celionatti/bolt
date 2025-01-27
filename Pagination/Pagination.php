<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - Pagination Class ============
 * ====================================
 */

namespace celionatti\Bolt\Pagination;


class Pagination
{
    protected $totalItems;
    protected $currentPage;
    protected $itemsPerPage;
    protected $totalPages;
    protected $urlPattern;
    protected $customClasses;

    public function __construct(array $paginationData, string $urlPattern = '', array $customClasses = [])
    {
        $this->totalItems = $paginationData['total_items'];
        $this->currentPage = $paginationData['current_page'];
        $this->itemsPerPage = $paginationData['items_per_page'];
        $this->totalPages = $paginationData['total_pages'];
        $this->urlPattern = $urlPattern;
        $this->customClasses = $customClasses;
    }

    protected function createPageUrl(int|string|float $pageNumber): string
    {
        $urlComponents = parse_url($this->urlPattern);
        $queryParams = [];

        // Parse existing query string if it exists
        if (isset($urlComponents['query'])) {
            parse_str($urlComponents['query'], $queryParams);
        }

        // Add or replace the page parameter
        $queryParams['page'] = (int) $pageNumber;

        // Rebuild the query string
        $queryString = http_build_query($queryParams);

        // Construct the final URL
        $baseUrl = $urlComponents['path'] ?? '';
        $finalUrl = $baseUrl . '?' . $queryString;

        return $finalUrl;
    }

    public function render(string $style = 'default')
    {
        if ($this->totalPages <= 1) {
            return '';
        }

        $paginationHtml = '';

        switch ($style) {
            case 'previous_next':
                $paginationHtml = $this->renderPreviousNext();
                break;
            case 'ellipses':
                $paginationHtml = $this->renderEllipses();
                break;
            case 'load_more':
                $paginationHtml = $this->renderLoadMore();
                break;
            case 'bootstrap':
                $paginationHtml = $this->renderBootstrap();
                break;
            case 'tailwind':
                $paginationHtml = $this->renderTailwind();
                break;
            default:
                $paginationHtml = $this->renderDefault();
                break;
        }

        return $paginationHtml;
    }

    protected function renderDefault()
    {
        $ulClass = $this->customClasses['ul'] ?? 'pagination';
        $liClass = $this->customClasses['li'] ?? '';
        $aClass = $this->customClasses['a'] ?? '';

        $html = '<ul class="' . $ulClass . '">';

        for ($i = 1; $i <= $this->totalPages; $i++) {
            $activeClass = ($i == $this->currentPage) ? 'active' : '';
            $html .= '<li class="' . $liClass . ' ' . $activeClass . '"><a class="' . $aClass . '" href="' . $this->createPageUrl($i) . '">' . $i . '</a></li>';
        }

        $html .= '</ul>';
        return $html;
    }

    protected function renderBootstrap()
    {
        $ulClass = $this->customClasses['ul'] ?? 'pagination';
        $liClass = $this->customClasses['li'] ?? 'page-item';
        $aClass = $this->customClasses['a'] ?? 'page-link';

        $html = '<ul class="' . $ulClass . '">';

        for ($i = 1; $i <= $this->totalPages; $i++) {
            $activeClass = ($i == $this->currentPage) ? 'active' : '';
            $html .= '<li class="' . $liClass . ' ' . $activeClass . '"><a class="' . $aClass . '" href="' . $this->createPageUrl($i) . '">' . $i . '</a></li>';
        }

        $html .= '</ul>';
        return $html;
    }

    protected function renderTailwind()
    {
        $ulClass = $this->customClasses['ul'] ?? 'flex justify-center';
        $liClass = $this->customClasses['li'] ?? '';
        $aClass = $this->customClasses['a'] ?? 'px-3 py-2 border border-blue-500';

        $html = '<ul class="' . $ulClass . '">';

        for ($i = 1; $i <= $this->totalPages; $i++) {
            $activeClass = ($i == $this->currentPage) ? 'bg-blue-500 text-white' : 'bg-white text-blue-500';
            $html .= '<li class="' . $liClass . '"><a class="' . $aClass . ' ' . $activeClass . '" href="' . $this->createPageUrl($i) . '">' . $i . '</a></li>';
        }

        $html .= '</ul>';
        return $html;
    }

    protected function renderPreviousNext()
    {
        $ulClass = $this->customClasses['ul'] ?? 'pagination';
        $liClass = $this->customClasses['li'] ?? '';
        $aClass = $this->customClasses['a'] ?? '';

        $html = '<ul class="' . $ulClass . '">';

        if ($this->currentPage > 1) {
            $html .= '<li class="' . $liClass . '"><a class="' . $aClass . '" href="' . $this->createPageUrl($this->currentPage - 1) . '">Previous</a></li>';
        }

        if ($this->currentPage < $this->totalPages) {
            $html .= '<li class="' . $liClass . '"><a class="' . $aClass . '" href="' . $this->createPageUrl($this->currentPage + 1) . '">Next</a></li>';
        }

        $html .= '</ul>';
        return $html;
    }

    protected function renderEllipses()
    {
        $ulClass = $this->customClasses['ul'] ?? 'pagination';
        $liClass = $this->customClasses['li'] ?? '';
        $aClass = $this->customClasses['a'] ?? '';

        $html = '<ul class="' . $ulClass . '">';

        // First page with active class check
        $firstActiveClass = ($this->currentPage == 1) ? 'active' : '';
        $html .= '<li class="' . $liClass . ' ' . $firstActiveClass . '">';
        $html .= '<a class="' . $aClass . '" href="' . $this->createPageUrl(1) . '">1</a>';
        $html .= '</li>';

        if ($this->currentPage > 3) {
            $html .= '<li class="' . $liClass . '"><a class="' . $aClass . '">...</a></li>';
        }

        // Middle pages
        for ($i = max(2, $this->currentPage - 1); $i <= min($this->totalPages - 1, $this->currentPage + 1); $i++) {
            $activeClass = ($i == $this->currentPage) ? 'active' : '';
            $html .= '<li class="' . $liClass . ' ' . $activeClass . '">';
            $html .= '<a class="' . $aClass . '" href="' . $this->createPageUrl($i) . '">' . $i . '</a>';
            $html .= '</li>';
        }

        if ($this->currentPage < $this->totalPages - 2) {
            $html .= '<li class="' . $liClass . '"><a class="' . $aClass . '">...</a></li>';
        }

        // Last page with active class check
        $lastActiveClass = ($this->currentPage == $this->totalPages) ? 'active' : '';
        $html .= '<li class="' . $liClass . ' ' . $lastActiveClass . '">';
        $html .= '<a class="' . $aClass . '" href="' . $this->createPageUrl($this->totalPages) . '">' . $this->totalPages . '</a>';
        $html .= '</li>';

        $html .= '</ul>';
        return $html;
    }

    protected function renderLoadMore()
    {
        $divClass = $this->customClasses['div'] ?? 'load-more';
        $aClass = $this->customClasses['a'] ?? '';

        $html = '';

        if ($this->currentPage < $this->totalPages) {
            $html .= '<div class="' . $divClass . '">';
            $html .= '<a class="' . $aClass . '" href="' . $this->createPageUrl($this->currentPage + 1) . '">Load More</a>';
            $html .= '</div>';
        }

        return $html;
    }
}
