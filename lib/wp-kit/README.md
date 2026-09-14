# ghostlabs/wp-kit — vendored source

Generic WordPress plumbing shared by GhostLabs plugins: a reflection-based DTO mapper, six
contracts, a polymorphic REST settings route and two small services. GPL-2.0-or-later, same as
this plugin. `LICENSE` is the upstream copy.

**Upstream:** `ghostlabs/wp-kit` **v1.0.3** — re-synced from that tag on 2026-08-28, the release
that carries the direct-access guards this copy had been patching in locally. (The first flatten,
2026-08-25, was byte-identical to the v1.0.1 copy Composer had installed; Undercard carries v1.0.2.
Releases are additive-only, so the election below keeps every copy honest on a site running
several.)

## Why this is source and not a Composer dependency

The free edition is published to WordPress.org together with a public source repository, and that
repository has to build for anyone who clones it. `ghostlabs/wp-kit` is distributed from a private
GitHub repository — an unauthenticated fetch 404s and it is not on Packagist — so a `require` on it
would make `composer install` fail for every reader, and a repository whose sources cannot be shown
to produce the shipped binaries is decoration.

Carrying the source instead removes the last credential this build needs. Free now installs with no
registry authentication of any kind, which matters beyond convenience: the fewer credentials a
build needs, the fewer reasons somebody else cannot build it. `VendoredKitTest` makes all of this
structural — no `repositories` block, no `ghostlabs/*` requirement, no shipping Composer package at
all, and every `GhostLabs\WPKit\` class the build imports present in this directory.

## How it loads

`shared/bootstrap.php` globs `lib/*` through the same loader-election pass as `vendor/ghostlabs/*`:
`wp-kit.php` registers this copy's version with `GhostLabsWPKitVersions` and only the newest copy
on the site initialises its autoloader, on `plugins_loaded` priority 0. An edition that installs
the package through Composer keeps its copy under `vendor/ghostlabs/` — same file, same election,
different directory — which is why the loader must keep registering the version it truly is.

## Do not add a PSR-4 entry for this

Upstream declares an **empty `autoload` block on purpose**, and the copy here drops the manifest
entirely. Composer never autoloads `GhostLabs\WPKit\`; `wp-kit.php` does, through the version gate.
Several GhostLabs plugins bundle their own copy, and **the newest registered one serves all of
them**. That is why the registered version string here is `'1.0.3'` and must stay honest: a copy
claiming a version it is not can win an election and then lack a class the real release of that
version has, which is a fatal in every plugin on the site compiled against it.

## Divergence from upstream v1.0.3

Three things, all deliberate and all docblock-only — nothing here changes what a line of this
library does, so the election's assumption that one version means one behaviour holds.

1. **`composer.json` and the upstream `README.md` are not copied.** Nothing installs this through
   Composer, and that README is the library's own development documentation — written for people
   working on the library rather than on a plugin that carries it.
2. **The class docblock of `src/Contracts/PluginIdentityInterface.php` is rewritten.** Upstream's
   explained the contract by naming the packages on the other side of the split, which a publicly
   distributed tier must not carry — and which upstream's own "nothing under src/ may mention the
   licence namespace" rule already argues against. Same wording as Undercard's copy.
3. **The constructor docblock of `src/RestRoute/AbstractSettingsRoutes.php` is rewritten** for the
   same reason. Same wording as Undercard's copy.

✅ *(was item 4)* **The `if ( ! defined( 'ABSPATH' ) ) exit;` guards are no longer a divergence.**
This copy carried them from 2026-08-28 as a local patch, after WordPress.org's Plugin Check errored
on `wp-kit.php` and `autoload.php` — the two files that execute on inclusion. Upstream **v1.0.3**
ships the same thirteen guards, so they now arrive with the re-sync. Two things worth keeping:

- ⚠️ `wp-kit.php`'s `! function_exists( 'add_action' )` fallback needs a harness that defines
  `ABSPATH` first — the guard's bare `exit` is status 0, so a bootstrap without the define gets a
  suite that reports success against no tests. Both of this plugin's unit bootstraps define it.
- ⚠️ This file **ships** — `build.sh`'s `/README.md` exclude is anchored to the plugin root and
  does not reach a nested one. Write it accordingly; `verify-tier-isolation.sh` reads it.

## Re-syncing

Copy `wp-kit.php`, `autoload.php`, `LICENSE` and `src/` from the new tag, drop `composer.json` and
`README.md`, then **re-apply the two docblock rewrites above** and update the version in this file
(the `**vX.Y.Z**` in the first paragraph is what `VendoredKitTest` reads).

The mechanical way, from the local clone of the package:

```bash
TAG=/tmp/wp-kit-tag && mkdir -p $TAG
git -C ~/Sites/theghostlab.io/composer-packages/ghostlabs-wp-kit archive vX.Y.Z \
    wp-kit.php autoload.php LICENSE src | tar -x -C $TAG
cp $TAG/wp-kit.php $TAG/autoload.php $TAG/LICENSE free-only/lib/wp-kit/
(cd $TAG && find src -type f \
    -not -path src/Contracts/PluginIdentityInterface.php \
    -not -path src/RestRoute/AbstractSettingsRoutes.php) \
  | while read -r f; do cp "$TAG/$f" "free-only/lib/wp-kit/$f"; done
diff -r free-only/lib/wp-kit $TAG -x README.md   # must name ONLY the two rewritten files
```

⚠️ **Run it from the repo root, and run the `diff` every time.** The paths above are relative, and
on 2026-08-28 the first re-sync was run from inside a tier folder by mistake: rsync obligingly
created a second `free-only/lib/wp-kit/` under that folder, copied the tag there, exited 0, and the
real copy was untouched — which the diff caught and nothing else would have. That stray tree then
rode into a commit on `git add -A` and had to be pulled back out. The `cp` loop is dumb on purpose;
the diff is the proof.

⚠️ And this file is scanned by `verify-tier-isolation.sh` like everything else that ships — the
first wording of the paragraph above named the tier folder and failed the gate.

⚠️ Keeping the two rewritten files is only safe while their *code* is unchanged upstream — the same
diff proves that too. If a tag changes either file's code, take upstream's and re-apply the docblock
by hand.

⚠️ `DirectAccessGuardTest` walks this directory, so a re-sync from any tag older than v1.0.3 fails
the suite immediately — deliberate.

A careless re-sync reintroduces the upstream docblocks at once. `VendoredKitTest` catches a
version/README mismatch and an incomplete copy, but the docblock rewrites are only guarded by the
release gate (`verify-tier-isolation.sh`) — it is far cheaper to land the rewrites in
`ghostlabs/wp-kit` itself, as Undercard's copy already proposes, so the next tag needs no
divergence at all.
