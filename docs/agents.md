# Agent Guide: Deterministic Test Scenarios

This package is built for human + AI workflows. Use the steps below whenever you need to
provision data for Pest/PHPUnit or E2E suites (Playwright, Cypress, etc.).

## Core Principles

1. **Deterministic data** – Every scenario must create fixed, human-readable fixtures.
   Examples: `admin@bforum.test` / `password`, `[SEC01] - Section 1`, `[SEC01:CAT01:THR01] - Thread 1`.
   Never rely on random factories or JSON outputs.
2. **Single source of truth** – The same scenario classes drive both unit tests and E2E provisioning.
3. **No HTTP helpers** – Always call the artisan provisioning command; never expose routes or dump
   JSON from controllers just to prime tests.

## Day-to-day Flow

1. **Inspect available scenarios**
   ```bash
   php artisan test-scenarios:list
   ```
   This prints every registered alias, the class behind it, and the docblock summary.
2. **Inspect available contexts and builders**
   ```bash
   php artisan test-contexts:list
   ```
   Register your contexts in `config/test-scenarios.php` so this command can display the
   public builder methods (e.g., `createAdmin`, `createSection`).
3. **Provision data before tests**
   ```bash
   php artisan test-scenarios:provision forum --seed
   ```
   - The command always runs `migrate:fresh` (no flag needed).
   - Pass `--seed` only if you want `Database\Seeders\DatabaseSeeder` to run after the migration.
   - Everything else should come from the scenario itself.
4. **Call from Playwright**
   Use the bundled helper (see README) to spawn the artisan command from your `global-setup.js` or
   `test.beforeAll`. Tests then look up fixtures by their deterministic labels.

## Writing Scenarios for Agents

- Add a short docblock above every scenario describing the actors/data it provides. The
  `test-scenarios:list` command surfaces this text to other agents.
- Inside `setup()`, create explicit fixtures with predictable keys/text. Prefer helper methods on
  the context class over inline factories so `test-contexts:list` stays accurate.
- When you add new builder methods to a context, keep them public and deterministic. Document
  parameters via PHPDoc or meaningful names.

## Docs on Demand

Agents can read this file together with the root README via:
```bash
php artisan test-scenarios:docs
```
Use `--section=agents` or `--section=readme` if you only need one part.
