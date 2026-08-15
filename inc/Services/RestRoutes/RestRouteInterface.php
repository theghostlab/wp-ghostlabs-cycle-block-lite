<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services\RestRoutes;

interface RestRouteInterface
{
    /**
     * Register this class's routes. Hooked to rest_api_init by Kernel for every
     * class under inc/ implementing this interface.
     *
     * @return void
     */
    public function routes();
}