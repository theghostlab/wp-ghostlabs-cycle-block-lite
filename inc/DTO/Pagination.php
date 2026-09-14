<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO;

if ( ! defined( 'ABSPATH' ) ) exit;

class Pagination
{
    public int $startingLimit = 0;
    public int $pageSize = 60;

    public static function fromPage(int $page, int $pageSize): self
    {
        $window = new self();

        $window->pageSize      = max(1, $pageSize);
        $window->startingLimit = (max(1, $page) - 1) * $window->pageSize;

        return $window;
    }
}
