# Cycle Block

Rotate content variations on a schedule — daily, weekly or monthly. One variation is chosen on
the server before the page is sent, so there is no flicker, no layout shift, and no duplicate
content in the markup.

Add a **Cycle** block, put a variation inside it for each version you want, pick an interval.
Anything can go in a variation: headings, images, buttons, columns, other blocks.

- **Shuffle**, so a full pass shows every variation before any repeats
- **Sequence**, if you want them in the order you wrote them
- **Repeat**, or stop on the last variation once the cycle has run through
- **Preview from the editor**, stepping through variations without waiting for the schedule
- **Per-post schedules** — a duplicated page cycles independently of its original

Requires **WordPress 6.4+** and **PHP 8.1+**.
Licensed **GPL-2.0-or-later**.

---

## What this repository is

The source for the plugin published on WordPress.org as
[`ghostlabs-cycle-block-lite`](https://wordpress.org/plugins/ghostlabs-cycle-block-lite/).

It contains the plugin exactly as distributed **plus** the uncompiled sources behind its
compiled assets — the `src/` the blocks are built from and the `dev/` the admin screen is built
from. The published bundles are compiled from these files, in this layout, so a rebuild here
reproduces them byte for byte rather than merely producing something equivalent.

You do not need any of this to use the plugin. Install it from WordPress.org.

### Layout

| Path | What it is |
|---|---|
| `ghostlabs-cycle-block.php` | plugin entry point |
| `inc/` | PHP — scheduling, persistence, render path, REST routes |
| `block-actions/` | block registration callbacks |
| `src/` | block editor sources (JSX) → compiled into `build/` |
| `dev/javascript/` | admin settings screen sources → compiled into `assets/js/` |
| `build/`, `assets/js/` | compiled output, as published |
| `vendor/` | Composer runtime dependencies |

---

## Building from source

```bash
composer install --no-dev --optimize-autoloader
pnpm install
pnpm run build
```

`pnpm run build` runs three pipelines: the blocks through webpack, the settings screen through
esbuild, and Tailwind for the settings stylesheet. The result overwrites `build/` and
`assets/js/`; both should come back identical to what is committed here.

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

## Cycle Block Pro

A paid edition adds per-variation analytics — a view and a click event per variation sent to
Google Analytics with your own parameters, plus an internal event log readable in the settings
screen. It is a separate product and no part of it is in this repository.

<https://theghostlab.io/cycle-block-pro>
