# E2E Provisioning Proposal

This document describes an adaptation of `antonioprimera/laravel-test-scenarios` so that the
existing scenarios can be reused outside PHPUnit / Pest and drive E2E test databases.

## Goals

- Allow scenarios to be executed without a `PHPUnit\Framework\TestCase` instance.
- Add one artisan command that provisions a database using any registered scenario.
- Offer a thin JavaScript helper that Playwright (or any Node-based runner) can call in setup hooks.
- Keep BC for the current Pest/PHPUnit workflow.

## 1. Make `TestScenario` runnable outside TestCase

1. Change the `TestScenario` constructor signature to accept `?PHPUnit\Framework\TestCase`.
   When no test case is given (CLI / JS provisioning), we still run `setup()` and expose the
   context exactly like in PHPUnit.
2. Guard every passthrough that truly needs a `TestCase` instance (assertion forwards,
   helpers such as `login`, etc.) and throw a descriptive exception if they are invoked without
   one. This keeps BC for unit tests (which keep passing `$this`) and gives a clear failure mode
   in development environments when someone needs PHPUnit-only helpers.
3. Expose a public factory (e.g. `ScenarioFactory::provision(string $scenario, array $data = [])`)
   that resolves the scenario out of the container, instantiates it without a `TestCase`, and
   returns the hydrated instance for CLI / Playwright consumers.

## 2. Artisan command: `test-scenarios:provision`

Signature draft:

```
php artisan test-scenarios:provision {scenario : FQCN or alias}
                                   {--data=}              # JSON payload or @path/to/file
                                   {--connection=}        # override DB connection for provisioning run
                                   {--seed}               # run DatabaseSeeder after migrate:fresh
                                   {--refresh-models}     # force refresh
                                   {--no-refresh-models}  # skip refresh
```

### Flow

1. Resolve scenario class. Support aliases via a new config file (`config/test-scenarios.php`) so
   E2E teams can address scenarios with short names.
2. Always run `artisan migrate:fresh` before provisioning (no seeders run unless `--seed` is provided).
3. Use the new factory to instantiate the scenario without a `TestCase` and pass `$data`.
4. `TestContext::refreshModels()` (opt-in) so DB state matches what the scenario expects.
5. Print a short confirmation message; scenarios must define deterministic data so no machine
   readable payload is emitted.

## 3. Publishing / configuration knobs

- `config/test-scenarios.php` keeps alias↔class mapping and default command options.
- Service provider publishes the config and registers the new command when `runningInConsole()`.
- Commands always refuse to run in `production` environments.

## 4. Playwright helper (Node)

Ship a small CommonJS helper alongside the composer package (`playwright/index.js`). The helper
spawns `php artisan test-scenarios:provision ...` and resolves once provisioning succeeds.

```js
const { provisionScenario } = require(
  '../../vendor/antonioprimera/laravel-test-scenarios/playwright'
);

module.exports = async () => {
  await provisionScenario('blog', {
    data: { locale: 'en' },
    artisanPath: '../artisan',   // defaults to process.cwd()/artisan
    env: { APP_ENV: 'e2e' },
  });
};
```

Implementation notes:

- Use `child_process.spawn` and manually pipe stdout so artisan output is visible.
- Surface non-zero exits as Playwright setup failures.
- No JSON parsing needed; tests should rely on deterministic fixtures referenced in scenario code.

## 5. DX for consuming apps

1. **Register scenarios**
   ```php
   // config/test-scenarios.php
   return [
       'aliases' => [
           'blog' => Tests\Scenarios\BlogScenario::class,
       ],
       'contexts' => [
           Tests\Context\BlogContext::class,
       ],
   ];
   ```

2. **Provision via CLI**
   ```bash
   php artisan test-scenarios:provision blog --data='{"locale":"en"}'
   ```
   Add `--seed` if you explicitly want to run `Database\\Seeders\\DatabaseSeeder` after
   the automatic fresh migration; otherwise scenarios stay the only source of fixtures.

3. **Playwright global setup**
   ```js
   const { provisionScenario } = require(
     '../../vendor/antonioprimera/laravel-test-scenarios/playwright'
   );

   module.exports = async () => {
     await provisionScenario('blog');
     process.env.E2E_ADMIN_EMAIL = 'admin@bforum.test';
   };
   ```

4. **Developer / agent helpers**
   ```bash
   php artisan test-scenarios:list        # discover scenario docstrings
   php artisan test-contexts:list         # inspect builder methods (requires contexts[] config)
   php artisan test-scenarios:docs --section=agents
   ```

## 6. Rollout plan

1. Update package internals (`TestScenario`, factory, service provider, config, command).
2. Provide automated tests for the factory + artisan command (run against sqlite memory DB).
3. Add documentation (README + this doc) describing CLI + Playwright integration.
4. Release as a minor bump (BC: constructor now accepts null, tests keep injecting `$this`).

This enables both artisan-driven provisioning as well as JS-based orchestration without locking us
into a specific E2E framework.
