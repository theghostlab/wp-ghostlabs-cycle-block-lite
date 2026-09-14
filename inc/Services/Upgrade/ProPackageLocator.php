<?php

declare(strict_types=1);

namespace GHOSTLABS\CYCLE_BLOCK\Services\Upgrade;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Asks the update service where the Pro package lives.
 *
 * The same plugin-updater endpoint the installed Pro edition polls, asked with
 * the same headers. Its download_url is complete and self-authorizing — a keyed
 * digest plus a six-hour single-window token — so it is handed to the installer
 * verbatim, never composed or rewritten here.
 */
final class ProPackageLocator
{
    /** @return array{ok: bool, downloadUrl: ?string, noticeCode: string, message: string} */
    public function locate(string $licenseKey): array
    {
        $response = wp_remote_get(UpgradeEndpoints::updateUri(), UpgradeFetch::args($licenseKey));

        if (is_wp_error($response)) {
            return $this->failure('network', __('The licensing service could not be reached. Please try again in a few minutes.', 'ghostlabs-cycle-block-lite'));
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $body   = json_decode(wp_remote_retrieve_body($response), true);

        $downloadUrl = is_array($body) ? trim((string) ($body['download_url'] ?? '')) : '';

        if ($status === 200 && $downloadUrl !== '') {
            if (!$this->isTrustedPackageUrl($downloadUrl)) {
                return $this->failure('download_untrusted', __('The Pro package location returned by the licensing service was not recognised as a trusted address, so the download was refused. Download the Pro plugin from your account page and install it manually.', 'ghostlabs-cycle-block-lite'));
            }

            return ['ok' => true, 'downloadUrl' => $downloadUrl, 'noticeCode' => '', 'message' => ''];
        }

        $reason = is_array($body) ? trim((string) ($body['message'] ?? '')) : '';

        $message = $reason === ''
            ? __('The Pro package is not available right now. Download it from your account page and install it manually.', 'ghostlabs-cycle-block-lite')
            : sprintf(
                /* translators: %s: the licensing service's own explanation. */
                __('The Pro package is not available for this key: %s', 'ghostlabs-cycle-block-lite'),
                $reason
            );

        return $this->failure('download_unavailable', $message);
    }

    /**
     * The package must be delivered over https from the very host that answered
     * this request — the backend serves both `/_service/plugin-updater` and the
     * `update-to-plugin/…` ZIP from one origin (license-manager's HomeUrlGenerator),
     * so an off-host or downgraded URL is a spoofed or tampered response, never a
     * legitimate one. Comparing against `updateUri()`'s host rather than a literal
     * means the release host rewrite retargets this check with everything else,
     * and dev keeps working against the dev host.
     */
    private function isTrustedPackageUrl(string $url): bool
    {
        $parts = wp_parse_url($url);

        if (!is_array($parts) || ($parts['scheme'] ?? '') !== 'https' || ($parts['host'] ?? '') === '') {
            return false;
        }

        $expectedHost = wp_parse_url(UpgradeEndpoints::updateUri(), PHP_URL_HOST);

        return is_string($expectedHost) && strcasecmp($parts['host'], $expectedHost) === 0;
    }

    /** @return array{ok: bool, downloadUrl: ?string, noticeCode: string, message: string} */
    private function failure(string $noticeCode, string $message): array
    {
        return ['ok' => false, 'downloadUrl' => null, 'noticeCode' => $noticeCode, 'message' => $message];
    }
}
