<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services\Upgrade;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * What one upgrade attempt came to, as data.
 *
 * Every failure is a value, never an exception or an HTTP error status: the
 * settings app renders `noticeCode`-specific guidance in its status region,
 * and a failure that arrived as anything but data is a failure it would show
 * as silence.
 */
final class UpgradeResult
{
    private function __construct(
        public readonly bool $ok,
        public readonly string $stage,
        public readonly string $noticeCode,
        public readonly string $message,
        public readonly ?string $activationUrl,
    ) {
    }

    public static function ok(string $activationUrl): self
    {
        return new self(true, 'complete', '', '', $activationUrl);
    }

    public static function fail(string $stage, string $noticeCode, string $message): self
    {
        return new self(false, $stage, $noticeCode, $message, null);
    }

    /** @return array{ok: bool, stage: string, noticeCode: string, message: string, activationUrl: ?string} */
    public function toArray(): array
    {
        return [
            'ok'            => $this->ok,
            'stage'         => $this->stage,
            'noticeCode'    => $this->noticeCode,
            'message'       => $this->message,
            'activationUrl' => $this->activationUrl,
        ];
    }
}
