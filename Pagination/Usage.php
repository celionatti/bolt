<?php

public function findAllByWithPagination(array $conditions, $page = null, $perPage, $orderByColumn, $orderDirection = 'asc')
{
    // ... (previous code)

    // Create a CustomPaginator instance
    $paginator = new CustomPaginator($totalCount, $perPage, $page);

    // Get the current URL (you may need to adjust this based on your framework)
    $currentUrl = $_SERVER['PHP_SELF'];

    // Generate pagination links
    $paginationLinks = $paginator->generateLinks($currentUrl);

    return [
        'data' => $data,
        'total' => $totalCount,
        'page' => $page,
        'perPage' => $perPage,
        'paginator' => $paginator,
        'paginationLinks' => $paginationLinks,
    ];
}


class CustomPaginator
{
    // ... (previous methods)

    public function generateLinks($url)
    {
        $links = '';

        if ($this->getTotalPages() > 1) {
            $links .= '<ul class="pagination justify-content-center">';

            if ($this->hasPreviousPage()) {
                $links .= '<li class="page-item"><a href="' . $url . '&p=' . $this->getPreviousPage() . '" class="page-link">&laquo;</a></li>';
            }

            $maxLinks = 5; // Maximum links to show before and after the current page
            $halfMaxLinks = floor($maxLinks / 2);
            $startPage = max(1, $this->currentPage - $halfMaxLinks);
            $endPage = min($this->getTotalPages(), $startPage + $maxLinks - 1);

            for ($i = $startPage; $i <= $endPage; $i++) {
                $activeClass = ($i == $this->currentPage) ? 'active' : '';
                $links .= '<li class="page-item ' . $activeClass . '"><a href="' . $url . '&p=' . $i . '" class="page-link">' . $i . '</a></li>';
            }

            if ($this->hasNextPage()) {
                $links .= '<li class="page-item"><a href="' . $url . '&p=' . $this->getNextPage() . '" class="page-link">&raquo;</a></li>';
            }

            $links .= '</ul>';
        }

        return $links;
    }
}
