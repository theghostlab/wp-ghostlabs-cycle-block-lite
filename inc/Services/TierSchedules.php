<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use GHOSTLABS\CYCLE_BLOCK\Boot\TierBootFactory;

if ( ! defined( 'ABSPATH' ) ) exit;

final class TierSchedules
{
    
    private const SECONDS = [
        'y' => 31536000,
        'm' => 2592000,
        'd' => 86400,
        'h' => 3600,
        'i' => 60,
        's' => 1,
    ];

    private array $allowed;

    public function __construct(?array $allowed = null)
    {
        $this->allowed = $allowed ?? TierBootFactory::current()->allowedSchedules();
    }

    public function clamp(string $schedule): string
    {
        if ($this->honours($schedule)) {
            return $schedule;
        }

        $seconds = self::seconds($schedule);

        if ($seconds === null) {
            return $schedule;
        }

        return $this->nearest($seconds) ?? $schedule;
    }

    public function clampParts(string $interval, string $frequency): array
    {
        $parts = explode('-', $this->clamp($interval . '-' . $frequency), 2);

        return [$parts[0], $parts[1] ?? $frequency];
    }

    public function honours(string $schedule): bool
    {
        return $this->allowed === [] || in_array($schedule, $this->allowed, true);
    }

    private function nearest(int $seconds): ?string
    {
        $best = null;
        $bestDistance = null;
        $bestSeconds = 0;

        foreach ($this->allowed as $candidate) {
            $candidateSeconds = self::seconds($candidate);

            if ($candidateSeconds === null) {
                continue;
            }

            $distance = abs($candidateSeconds - $seconds);

            if ($bestDistance === null
                || $distance < $bestDistance
                || ($distance === $bestDistance && $candidateSeconds > $bestSeconds)
            ) {
                $best = $candidate;
                $bestDistance = $distance;
                $bestSeconds = $candidateSeconds;
            }
        }

        return $best;
    }

    private static function seconds(string $schedule): ?int
    {
        if (!preg_match('/^(\d+)-([a-z])$/', $schedule, $matches)) {
            return null;
        }

        $frequency = $matches[2];

        if (!isset(self::SECONDS[$frequency])) {
            return null;
        }

        return (int) $matches[1] * self::SECONDS[$frequency];
    }
}
