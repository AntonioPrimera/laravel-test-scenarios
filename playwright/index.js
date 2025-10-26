const { spawn } = require('node:child_process');
const path = require('node:path');

/**
 * Provision a Laravel test scenario by shelling out to artisan.
 * The helper does NOT return IDs; tests should rely on deterministic data
 * defined inside the scenario classes.
 *
 * @param {string} scenario alias or FQCN defined in config/test-scenarios.php
 * @param {object} [options]
 * @param {string} [options.artisanPath] Path to the artisan executable (defaults to ./artisan)
 * @param {string} [options.phpBinary] PHP binary to use (defaults to `php`)
 * @param {object} [options.data] Arbitrary payload forwarded to the scenario setup
 * @param {string} [options.connection] Override the DB connection for this run
 * @param {boolean|null} [options.refreshModels] Force refreshing (true), skip refreshing (false) or use config (null/undefined)
 * @param {object} [options.env] Extra environment variables
 * @param {string} [options.cwd] Working directory for the artisan call
 * @param {number} [options.timeoutMs] Optional timeout in milliseconds
 * @param {boolean} [options.silent] When true, do not pipe artisan stdout to the parent process
 * @returns {Promise<void>} Resolves when provisioning succeeds
 */
async function provisionScenario(scenario, options = {}) {
	if (!scenario)
		throw new Error('Scenario name or alias is required.');

	const {
		artisanPath = path.join(process.cwd(), 'artisan'),
		phpBinary = 'php',
		data,
		connection,
		refreshModels,
		env = {},
		cwd = path.dirname(path.resolve(artisanPath)),
		timeoutMs,
		silent = false,
	} = options;

	const resolvedArtisan = path.resolve(artisanPath);
	const args = [resolvedArtisan, 'test-scenarios:provision', scenario];

	if (data !== undefined) {
		const serialized = typeof data === 'string' ? data : JSON.stringify(data);
		args.push(`--data=${serialized}`);
	}

	if (connection)
		args.push(`--connection=${connection}`);

	if (refreshModels === true)
		args.push('--refresh-models');
	else if (refreshModels === false)
		args.push('--no-refresh-models');

	const child = spawn(phpBinary, args, {
		cwd,
		env: { ...process.env, ...env },
		stdio: ['inherit', 'pipe', 'inherit'],
	});

	child.stdout.on('data', chunk => {
		if (!silent)
			process.stdout.write(chunk);
	});

	await new Promise((resolve, reject) => {
		const timers = [];

		if (timeoutMs) {
			const timeout = setTimeout(() => {
				child.kill('SIGTERM');
				reject(new Error(`Provisioning command timed out after ${timeoutMs}ms`));
			}, timeoutMs);

			timers.push(timeout);
		}

		child.on('error', reject);
		child.on('close', code => {
			timers.forEach(clearTimeout);

			if (code !== 0)
				return reject(new Error(`Provisioning command exited with code ${code}`));

			resolve();
		});
	});
}

module.exports = {
	provisionScenario,
};
