<?php

namespace THEGHOSTLAB\CYCLE\Services;

class Transients
{
    static public function clearCollection($transient): bool {
        return delete_transient($transient);
    }

    static public function setCollection(string $transient, array $collection, $expiration = HOUR_IN_SECONDS): bool {
        return set_transient( $transient, $collection, $expiration );
    }

    static public function getCollection(string $transient) : array
    {
        if (false === ($collection = get_transient( $transient ))) {
            $collection = [];
            set_transient($transient, $collection, HOUR_IN_SECONDS);
        }

        return $collection;
    }
}