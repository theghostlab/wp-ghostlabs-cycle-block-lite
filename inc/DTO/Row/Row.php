<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Row;

use GhostLabs\WPKit\Service\DTOMapper;

/**
 * Base for the typed row shapes that cross DBService.
 *
 * Properties are snake_case on purpose — they mirror the table columns exactly,
 * so DTOMapper::map() hydrates a row straight from a DB read and toRow() feeds
 * DBService::insertOnUpdate() without a key translation step.
 */
abstract class Row
{
    /**
     * The row as column => value, with null properties dropped. That filter is
     * load-bearing: insertOnUpdate() only touches the columns it is given, so a
     * partial update (say, only last_displayed) must not carry nulls that would
     * overwrite the columns it left out.
     *
     * @return array<string, mixed>
     */
    public function toRow(): array
    {
        return array_filter(DTOMapper::toArray($this), static fn($value) => $value !== null);
    }
}
