<?php

namespace GHOSTLABS\CYCLE_BLOCK\DTO\Input;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The upgrade endpoint's payload: the licence key the admin pasted. The null
 * default is load-bearing — see BlockRequestDto.
 */
class UpgradeRequestDto
{
    public ?string $licenseKey = null;
}
