# Upgrade to PHP 8.4 / Nette 3.2 / Latte 3

Applies to upgrading from 0.1.x – 0.3.x (Nette 2.4, Latte 2, PHP 7) to the release after 0.3.1.

## Requirements
| | before | now |
|---|---|---|
| PHP | ^7.2 | >= 8.3 (tested on 8.4) |
| nette/application, forms, di | ^2.4 \| ^3.0 | ^3.2 |
| nette/utils | ^2.0 \| ^3.0 | ^4.0 |
| latte/latte | ^2.4 | ^3.0.18 (3.0 and 3.1) |
| nette/php-generator | ^2.0 \| ^3.0 | no longer required directly |
| doctrine/dbal | (implicit) | suggested ^3.10, only for `GpsCoordsType` |
| doctrine/orm | (implicit) | suggested ^2.20, only for `GpsTrait` |

## What changed in the library
- **DI extension** — config schema via `getConfigSchema()` (`mapApiKey`, `suggestionApiKey`, both strings, default `''`; unknown keys now fail), the factory is a Nette 3 factory definition (`addFactoryDefinition`), form methods are registered through `$this->initialization` (no `afterCompile()` / `$class->methods`).
- **Form controls** `GpsPicker`, `AddressSuggestion` — no `ITemplate`, no template created in `monitor()`. The template is rendered on `getControl()` through the presenter's template factory, or a plain Latte engine when the form is not attached to a presenter. Typed signatures (`string|Stringable|null $label`, `?int $maxLength`).
- **GpsPicker**
  - `setValue()` accepts `GpsCoords` (stored as the string `"lat lng"`), string or null.
  - Validation: besides the format pattern (now allows negative values) a new rule checks the range of the coordinates (`Neplatné GPS souřadnice.`), so `getGps()` after a successful submit does not throw.
  - `getGps()` parses with `GpsCoords::fromString()` (any whitespace between the values, optional `N`/`E` suffix).
- **Templates (Latte 3)** — the input is passed as `$input` (the `$control` variable is reserved for `Nette\Application\UI\Control` in Nette 3 templates), inline scripts are wrapped in an IIFE (several pickers / suggestions on one page or re-executed AJAX snippets no longer redeclare `let suggestion` / `createMap`), the `style` of the map uses `n:attr` (Latte CSS escaping turned `100%` into `100\%`).
- **`Clear01\Maps\Exceptions\MapException`** now exists (it was referenced by `GpsCoords` but missing, so an invalid coordinate ended with "Class not found"). It extends `InvalidArgumentException`, constants `INVALID_LATITUDE`, `INVALID_LONGITUDE`.
- **`GpsCoords`** — typed properties, fluent setters return `static`, new `GpsCoords::fromString(?string): ?GpsCoords`.
- **`Marker`** — implements `Stringable`, new `getData()`, JSON without the deprecated int flags of `Json::encode()`.
- **`Settings`** — defaults in the constant `Settings::DEFAULTS` (was a protected property which leaked into the ArrayHash iteration).
- **`MapControl`** — `render(): void`, new `getSettings()`, `random_int()` for the identifier.
- **`GpsCoordsType`** (DBAL 3) — `getSQLDeclaration(array $column, AbstractPlatform $platform): string` via `getStringTypeDeclarationSQL()` (VARCHAR(50)), `requiresSQLCommentHint()` = true, `getName(): string`. `convertToDatabaseValue()` returns **NULL for null / ''** (it returned `''` before) and throws `ConversionException` for an invalid value; `convertToPHPValue()` returns null for malformed or out of range values.
- **`GpsTrait`** — PHP attributes `#[ORM\Column]` next to the annotations, so it works with annotation and attribute mapping.

## What to change in the application
1. `composer.json`: require the new tag of `clear01/maps`; add `doctrine/dbal` ^3.10 / `doctrine/orm` ^2.20 yourself (they are only suggested).
2. Config stays the same (`extensions: maps: Clear01\Maps\DI\MapsExtension` + section `maps:`); remove keys other than `mapApiKey` / `suggestionApiKey`.
3. Register `gps_coords: Clear01\Maps\Doctrine\GpsCoordsType` in the types of the new Doctrine integration (e.g. `nettrine.dbal: connection: types:`), if it is still needed.
4. Templates using the suggestion event: no `{$var}` inside JS string literals (Latte 3 compile error) — use `"#" + {$form['street']->getHtmlId()}`.
5. `catch (MapException)` now works as expected; code relying on `GpsCoordsType` writing `''` for null now gets NULL.
6. JS assets (`assets/*.js`) did not change; they do not depend on nette.ajax or netteForms.
