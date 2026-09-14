<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Input;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The claim-upgrade payload: the one-shot token the checkout redirect carried.
 * The null default is load-bearing — see BlockRequestDto.
 */
class ClaimRequestDto
{
    public ?string $claimToken = null;
}
