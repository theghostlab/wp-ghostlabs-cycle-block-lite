<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services;

if ( ! defined( 'ABSPATH' ) ) exit;

final class PreviewStyle
{
    
    public const LABEL_PROPERTY = '--ghostlabs-cycle-preview-label';

    public static function inlineCss(): string
    {
        return sprintf(
            ':root{%s:"%s"}',
            self::LABEL_PROPERTY,
            self::cssString(__('Cycle Preview:', 'ghostlabs-cycle-block-lite'))
        );
    }

    private static function cssString(string $label): string
    {
        return addcslashes(
            (string) preg_replace('/[\r\n\f]+/', ' ', $label),
            "\\\""
        );
    }
}
