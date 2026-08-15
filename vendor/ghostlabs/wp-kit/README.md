# ghostlabs/wp-kit

Generic WordPress plumbing shared by GhostLabs plugins: a reflection-based DTO mapper, six
contracts, a polymorphic REST settings route, and two small services. **No licence code, no update
checker, no network calls.**

Licensed **GPL-2.0-or-later**, which is the point: this is the half of the old
`ghostlabs/wp-shared` that a free plugin ships. A GhostLabs free tier bound for WordPress.org has to
be GPL-compatible all the way down, including everything inside `vendor/`, and the proprietary
licence-enforcement layer lives in [`ghostlabs/wp-license`](#relationship-to-ghostlabswp-license)
where a free build never sees it.

---

## Requirements

- PHP 8.1+
- WordPress, for the two classes that touch it (`AbstractSettingsRoutes`, `Utils`). `DTOMapper` and
  every contract are pure PHP and run under plain PHPUnit with no bootstrap.

---

## Installation

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:theghostlab/ghostlabs-wp-kit.git"
        }
    ],
    "require": {
        "ghostlabs/wp-kit": "^1.0"
    }
}
```

```bash
composer install
```

Then require the loader from the plugin's bootstrap, **before** the Composer autoloader:

```php
require_once __DIR__ . '/vendor/ghostlabs/wp-kit/wp-kit.php';
require_once __DIR__ . '/vendor/autoload.php';
```

This package declares an **empty Composer `autoload` block on purpose** — see
[Version-gated loader](#version-gated-loader). Composer will never autoload `GhostLabs\WPKit\`
classes; `wp-kit.php` does.

---

## Package Structure

```
src/
  Contracts/
    DTOValidationInterface.php          # static rules(): array
    MutableOptionsServiceInterface.php  # adds save() / delete()
    OptionsServiceInterface.php         # get(string): mixed
    PluginIdentityInterface.php         # the licence-free half of plugin config
    RestRouteInterface.php              # routes(): void
    SaveSettingsHandlerInterface.php    # save(string): ?array
  RestRoute/
    AbstractSettingsRoutes.php          # polymorphic POST /save-settings endpoint
    CollectRoute.php                    # PHP attribute for route auto-discovery
  Service/
    DTOMapper.php                       # reflection-based mapping + validation
    SaveSettings.php                    # per-key save-handler dispatcher
    Utils.php                           # REST payload sanitisation
```

Eleven files, six of them interfaces. Keeping that surface small is deliberate: the version-gated
loader means the newest copy on a site serves every plugin on it, so every class here is a
permanent commitment.

---

## Version-gated loader

`wp-kit.php` is the entry point, borrowed from Action Scheduler. Several GhostLabs plugins bundle
their own copy of this package; each requires this file at bootstrap. At `plugins_loaded` priority
**0**, `GhostLabsWPKitVersions::initializeLatest()` sorts every registered version with
`version_compare` and calls **only the newest one's** initializer. Exactly one `autoload.php` runs
and exactly one `spl_autoload_register` closure serves `GhostLabs\WPKit\` for the whole site.

Four consequences worth knowing before you touch anything:

1. **The registry elects a copy, not a set.** Losing copies never register an autoloader; there is
   no fallback chain. The winning copy must therefore be a superset of every other copy installed
   on that site.
2. **Releases are additive-only.** A class removed or renamed in version *N* is a fatal error in
   every plugin on the site still compiled against *N−1* — including plugins that were never
   updated and whose own `composer.lock` pins the older version. Composer resolves per plugin; PHP
   resolves per site. This has happened once already in this codebase's history (see `CLAUDE.md`).
3. **The autoloader fails silently.** `autoload.php` guards its `require_once` with `file_exists()`,
   so a missing class produces no diagnostic from this package at all — just PHP's own
   `Error: Class "…" not found` from wherever it was first touched.
4. **Register at include time, not inside a hook.** Every copy on the site must have registered
   before any initializer runs. Plugin files are all included before `plugins_loaded` fires, which
   is what makes the election deterministic.

Outside WordPress — unit tests, plain CLI — there is no `add_action`, so `wp-kit.php` requires
`autoload.php` immediately and returns. There is no gate outside WordPress.

**Never touch a `GhostLabs\WPKit\` class at include time.** A `use` statement is free, but
instantiating, `implements`-resolving or reflecting one before priority 0 fatals no matter how
correct the loader is. For the same reason, a consumer must not hook its own boot at
`plugins_loaded` **0** — same priority means order is registration order, which is plugin load
order.

---

## Relationship to `ghostlabs/wp-license`

```
ghostlabs/wp-license  ──requires──▶  ghostlabs/wp-kit
```

One way, always. `wp-kit` must never reference `GhostLabs\WPLicense\`;
`tests/DependencyDirectionTest.php` greps `src/` and fails if it ever does.

The two packages have **separate namespace roots, separate registry classes and separate
elections**. A `GhostLabs\WPKit\…` lookup can only ever be answered by a wp-kit copy. Nothing one
package's election does can affect the other's, which is exactly why the split is safe on a site
running a mix of migrated and unmigrated plugins.

Neither package declares `replace: {"ghostlabs/wp-shared": …}`, and neither ever may — see
`CLAUDE.md`.

---

## Contracts Reference

### `PluginIdentityInterface`

The licence-free half of a plugin's configuration. Carved out of `ghostlabs/wp-shared`'s
`ConfigInterface`; the four members that stayed behind (`getCacheKey()`, `getTransientKey()`,
`getUpdateUrl()`, `getBlacklist()`) exist only for licence verification and self-hosted updates and
live in the licence package's `ConfigInterface`, which **extends this one**.

```php
interface PluginIdentityInterface
{
    public function getPluginFile(): string;       // "my-plugin/my-plugin.php"
    public function getUpdaterSlug(): string;      // "my-plugin"
    public function getVersion(): string;          // "1.0.0"
    public function getRestNamespace(): string;    // "ghostlabs/glide/pro/v1"
    public function getDomain(): string;
    public function getSettingsMenuSlug(): string;
}
```

A paid plugin's existing `PluginConfigAdapter implements ConfigInterface` satisfies this by
inheritance — no second adapter, no second `use` line at the implementation site.

### `OptionsServiceInterface` / `MutableOptionsServiceInterface`

```php
interface OptionsServiceInterface
{
    public function get(string $key): mixed;
}

interface MutableOptionsServiceInterface extends OptionsServiceInterface
{
    public function save(string $key, mixed $data): bool;
    public function delete(string $key): ?bool;    // null when the option store does not exist
}
```

Each consumer's `OptionsService` must use its **own** `wp_option` key. Never share one across
plugins — in Multisite with network-activated plugins that is cross-plugin data loss.

### `DTOValidationInterface`

```php
interface DTOValidationInterface
{
    /** @return array<string, array<string, mixed>> */
    static public function rules(): array;
}
```

### `RestRouteInterface`

```php
interface RestRouteInterface
{
    public function routes(): void;
}
```

Used with the `#[CollectRoute]` attribute for auto-discovery.

### `SaveSettingsHandlerInterface`

```php
interface SaveSettingsHandlerInterface
{
    /** @return array<string, mixed>|null */
    public function save(string $data): ?array;
}
```

One handler per settings key, dispatched by `SaveSettings`.

---

## `DTOMapper`

| Method | Purpose |
|---|---|
| `map(array $data, string $dtoClass)` | Maps array data onto a DTO by reflection. Casts `int`, `float`, `bool`, `array`, `string`; coerces enums; recurses into nested DTO classes. |
| `mapNested(array $data, string $dtoClass, array $nestedMappings)` | `map()` plus nested DTOs and collections. |
| `validate(object $dto, DTOValidationInterface\|array $rules)` | Rules: `required`, `min`, `max`, `email`, `regex`, `enum`, `nested`. |
| `toArray(object $dto)` | Recurses into nested objects. |

The `enum` rule accepts a PHP enum class name or a plain allow-list array:

```php
'status' => ['required' => true, 'enum' => OrderStatus::class],  // backed: matches case ->value
'mode'   => ['enum' => RenderMode::class],                       // pure: matches case ->name
'sort'   => ['enum' => ['asc', 'desc']],                         // strict in_array
```

**Declare enum-typed DTO properties as nullable.** `map()` coerces an illegal value to `null`
rather than assigning it raw, because assigning a string to a typed enum property throws a
`TypeError`. And because `map()` runs *before* `validate()` and has already turned bad input into
`null`, **pair `enum` with `required`** — without it, an illegal value silently becomes `null` and
validation passes.

**Every DTO property needs a null default.** `validate()` reads properties directly, so an
uninitialized typed property turns an incomplete payload into a 500 instead of a 412.

---

## `AbstractSettingsRoutes`

A polymorphic `POST /save-settings` endpoint (capability: `manage_options`). Consumers implement
two methods:

- **`dtoMap()`** — maps the request `type` value to `[DtoClass, $rules]`. The REST `type` arg's
  `enum` is auto-derived from its keys.
- **`handlerMap()`** — maps DTO property keys to `SaveSettingsHandlerInterface` classes.

Its constructor is typed against **`PluginIdentityInterface`, not the licence `ConfigInterface`** —
it calls exactly one config member, `getRestNamespace()`. That is what lets a free tier mount a
settings screen with a working save endpoint without installing the licence package, which is the
whole reason this class sits in `wp-kit` rather than beside the licence routes. A `ConfigInterface`
instance still satisfies the narrower hint, so paid consumers notice nothing.

Override `routeArgs()` to add REST args beyond `type`.

---

## Development

```bash
composer install
composer test      # PHPUnit
composer analyse   # PHPStan level 6 (--memory-limit=1G)
```

There is no build step. See `CLAUDE.md` for the release checklist and the invariants that must hold
before tagging.
