# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

## [v2.1.0] - 2025-10-26
- Added deterministic-scenario agent guide (`docs/agents.md`) and artisan-accessible docs via `test-scenarios:docs`.
- Introduced scenario/context discovery commands (`test-scenarios:list`, `test-contexts:list`) powered by reflection docblocks.
- Simplified `test-scenarios:provision` to always run `migrate:fresh`, block production, and emit a concise confirmation (no JSON output).
- Added Playwright helper + documentation updates to reinforce CLI-only provisioning and deterministic fixtures.
- Created config knobs for registering aliases/contexts and ensured service provider publishes/merges them.
- Expanded test suite to cover new commands, provisioning behavior, and nullable TestCase support.
