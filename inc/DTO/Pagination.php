<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO;

/**
 * The LIMIT window for a paged read through DBService::selectAll().
 *
 * Values are clamped at construction so an interpolated "LIMIT %d, %d" can never
 * see a negative offset or a zero page size — either would make MySQL reject the
 * statement outright.
 */
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
