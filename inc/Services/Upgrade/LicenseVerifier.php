<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services\Upgrade;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * A GPL licence check for the upgrade path — a plain wp_remote_post client.
 *
 * Deliberately NOT the proprietary wp-license handler: that package must never
 * enter this edition's tree, which WordPress.org publishes source and all. This
 * client also deliberately caches nothing — it runs once, on an explicit admin
 * action, and the edition that owns verdict caching is the one being installed.
 */
final class LicenseVerifier
{
    /**
     * Verdict codes that mean "renew", mirroring Pro's blacklist semantics:
     * 17 subscription cancelled, 83 expired.
     */
    private const RENEWAL_CODES = [17, 83];

    /**
     * Statuses the auth layer answers for a bearer it does not know — the
     * verdict pipeline is never reached, so there is no JSON body to read.
     */
    private const UNRECOGNIZED_STATUSES = [401, 403, 404];

    /** @return array{ok: bool, noticeCode: string, message: string} */
    public function verify(string $licenseKey): array
    {
        $response = wp_remote_post(UpgradeEndpoints::licenseCheck(), UpgradeFetch::args($licenseKey));

        if (is_wp_error($response)) {
            return $this->failure('network', __('The licensing service could not be reached. Please try again in a few minutes.', 'ghostlabs-cycle-block-lite'));
        }

        $status = (int) wp_remote_retrieve_response_code($response);

        if (in_array($status, self::UNRECOGNIZED_STATUSES, true)) {
            return $this->failure('key_not_recognized', __('That key was not recognized. Check it against your receipt email.', 'ghostlabs-cycle-block-lite'));
        }

        $verdict = json_decode(wp_remote_retrieve_body($response), true);

        if ($status !== 200 || !is_array($verdict)) {
            return $this->failure('network', __('The licensing service answered unexpectedly. Please try again in a few minutes.', 'ghostlabs-cycle-block-lite'));
        }

        if (($verdict['valid'] ?? false) === true) {
            return ['ok' => true, 'noticeCode' => '', 'message' => ''];
        }

        $code = (int) ($verdict['code'] ?? 0);

        if (in_array($code, self::RENEWAL_CODES, true)) {
            return $this->failure('renewal', __('This key has expired or its subscription was cancelled. Renew it to upgrade.', 'ghostlabs-cycle-block-lite'));
        }

        if ($code === 404) {
            return $this->failure('key_not_recognized', __('That key was not recognized. Check it against your receipt email.', 'ghostlabs-cycle-block-lite'));
        }

        $reason = trim((string) ($verdict['message'] ?? ''));

        $message = $reason === ''
            ? __('This key cannot be used to upgrade this site.', 'ghostlabs-cycle-block-lite')
            : sprintf(
                /* translators: %s: the licensing service's own explanation. */
                __('This key cannot be used to upgrade this site: %s', 'ghostlabs-cycle-block-lite'),
                $reason
            );

        return $this->failure('key_refused', $message);
    }

    /** @return array{ok: bool, noticeCode: string, message: string} */
    private function failure(string $noticeCode, string $message): array
    {
        return ['ok' => false, 'noticeCode' => $noticeCode, 'message' => $message];
    }
}
