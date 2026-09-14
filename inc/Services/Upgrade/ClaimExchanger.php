<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services\Upgrade;

use GHOSTLABS\CYCLE_BLOCK\Config\PluginConfig;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Redeems a checkout's claim token for the licence key, server to server.
 *
 * Only the claim ever rode the browser redirect; only this call carries the
 * key. It is the plugin's one unauthenticated outbound call — there is no key
 * to bearer yet, which is why the claim route lives outside /_service/.
 *
 * Every refusal collapses into one recovery — paste the key from the receipt
 * email — because the server deliberately answers unknown, expired, spent and
 * wrong-domain claims identically.
 */
final class ClaimExchanger
{
    /** @return array{ok: bool, licenseKey: ?string, noticeCode: string, message: string} */
    public function exchange(string $claimToken): array
    {
        $host = wp_parse_url(get_site_url(), PHP_URL_HOST);

        $response = wp_remote_post(UpgradeEndpoints::claimLicenseUri(), [
            'timeout' => 45,
            'headers' => [
                'Domain'       => is_string($host) ? $host : '',
                'Plugin-Slug'  => PluginConfig::$_slug,
                'Content-Type' => 'application/json',
            ],
            'body' => (string) wp_json_encode(['token' => $claimToken]),
        ]);

        if (is_wp_error($response)) {
            return $this->failure('network', __('The licensing service could not be reached. Please try again in a few minutes.', 'ghostlabs-cycle-block-lite'));
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        $licenseKey = is_array($body) ? trim((string) ($body['licenseKey'] ?? '')) : '';

        if ((int) wp_remote_retrieve_response_code($response) === 200 && $licenseKey !== '') {
            return ['ok' => true, 'licenseKey' => $licenseKey, 'noticeCode' => '', 'message' => ''];
        }

        return $this->failure('claim_gone', __('This upgrade link has expired or was already used. Paste the license key from your receipt email instead.', 'ghostlabs-cycle-block-lite'));
    }

    /** @return array{ok: bool, licenseKey: ?string, noticeCode: string, message: string} */
    private function failure(string $noticeCode, string $message): array
    {
        return ['ok' => false, 'licenseKey' => null, 'noticeCode' => $noticeCode, 'message' => $message];
    }
}
