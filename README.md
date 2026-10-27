# Cycle Block — source repository

This is the source of **Cycle Block**, the free WordPress plugin published on WordPress.org as
[`ghostlabs-cycle-block-lite`](https://wordpress.org/plugins/ghostlabs-cycle-block-lite/). It is
the source, not the product page: to use the plugin, install it from WordPress.org; to read what
it does, see [`readme.txt`](readme.txt) here or the listing there.

Rotate content variations on a schedule — daily, weekly or monthly. Rendered server-side, so
there's no flicker or layout shift.

Requires **WordPress 6.8+** and **PHP 8.1+**. Licensed **GPL-2.0-or-later**.

---

## What this repository contains

The plugin's source at the version the newest tag names — the same PHP that ships — **plus** the
uncompiled sources behind its compiled assets: the `src/` the blocks are built from and the
`dev/javascript/` the settings screen is built from. The bundles committed here are compiled from
these files, in this layout, so a rebuild here reproduces them byte for byte rather than merely
producing something equivalent.

The code ships unminified, and stripped of prose comments except where a comment does a job: the
plugin header WordPress reads the plugin's identity from, `translators:` notes the i18n tooling
needs, `phpcs:` and `phpstan-` annotations, third-party licence banners — and the whole
whole upgrade path under `inc/Services/Upgrade/`, which keeps its comments deliberately, because
it is the flow worth reading closely.

| Path | What it is |
|---|---|
| `ghostlabs-cycle-block.php` | plugin entry point |
| `inc/` | PHP — scheduling, persistence, render path, REST routes |
| `block-actions/` | block registration callbacks |
| `src/` | block editor sources (JSX) → compiled into `build/` |
| `dev/javascript/` | settings screen sources → compiled into `assets/js/` |
| `build/`, `assets/js/`, `assets/css/settings.css` | compiled output, as published |
| `assets/css/theghostlab-cycle.css` | the front-end stylesheet, hand-written |
| `lib/wp-kit/` | the one GhostLabs library the plugin uses, carried as readable GPL-2.0-or-later source |
| `vendor/` | Composer's autoloader — the plugin has no third-party PHP dependencies |
| `readme.txt`, `LICENSE`, `index.php` | the WordPress.org listing text, the GPL-2.0 licence, and the directory guard |

---

## Building from source

```bash
composer install --no-dev --optimize-autoloader
pnpm install
pnpm run build
```

`pnpm run build` runs three pipelines: the blocks through webpack, the settings screen through
esbuild, and Tailwind for the settings stylesheet. The result overwrites `build/`,
`assets/js/` and `assets/css/settings.css`; all three should come back identical to what is
committed here.

⚠️ **`composer install` must not use `--classmap-authoritative`.** It makes the generated
classmap the only answer, and the autoloader relies on its PSR-4 fallback.

Everything here builds from public packages. There are no private registries and no
credentials involved — if `pnpm install` fails for you, that is a bug and we would like to hear
about it.

---

## Reporting a problem

Issues and pull requests are welcome on this repository. For anything security-related, please
email **dev@theghostlab.io** rather than opening a public issue.

---

## The paid edition

Cycle Block Pro is a separate product, and no part of it is in this repository:
<https://theghostlab.io/cycle-block>
