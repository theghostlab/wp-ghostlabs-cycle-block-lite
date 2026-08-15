<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

class Transients
{
    /**
     * @param string $transient
     */
    static public function clearCollection($transient): bool {
        return delete_transient($transient);
    }

    /**
     * @param array<mixed> $collection
     * @param int $expiration
     */
    static public function setCollection(string $transient, array $collection, $expiration = HOUR_IN_SECONDS): bool {
        return set_transient( $transient, $collection, $expiration );
    }

    /**
     * @return array<mixed>
     */
    static public function getCollection(string $transient) : array
    {
        if (false === ($collection = get_transient( $transient ))) {
            $collection = [];
            set_transient($transient, $collection, HOUR_IN_SECONDS);
        }

        return $collection;
    }
}