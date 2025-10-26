<?php
namespace AntonioPrimera\TestScenarios\Commands;

use AntonioPrimera\TestScenarios\ScenarioFactory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use JsonException;
use RuntimeException;

class ProvisionScenario extends Command
{
	protected $signature = 'test-scenarios:provision
		{scenario : FQCN or alias defined in config/test-scenarios.php}
		{--data= : JSON string (or @path/to/file) forwarded to the scenario setup}
		{--seed : Run DatabaseSeeder after migrate:fresh}
		{--connection= : Override the default database connection}
		{--refresh-models : Force refreshing context models after setup}
		{--no-refresh-models : Skip refreshing context models after setup}';

	protected $description = 'Provision the database using a Laravel test scenario.';

	public function __construct(protected ScenarioFactory $factory)
	{
		parent::__construct();
	}

	public function handle(): int
	{
		$this->guardEnvironment();

		$connection = $this->option('connection');
		$this->configureConnection($connection);

		$this->runFreshMigrations($connection);

		$setupData = $this->parseDataOption($this->option('data'));

		$scenario = $this->factory->make($this->argument('scenario'), $setupData);

		if ($this->shouldRefreshModels())
			$scenario->refreshModels();

		$connectionName = $connection ?: config('database.default');
		$scenarioClass = $scenario::class;
		$this->components->info("Provisioned [{$scenarioClass}] on connection [{$connectionName}].");

		return self::SUCCESS;
	}

	protected function guardEnvironment(): void
	{
		if (App::environment('production'))
			throw new RuntimeException('Refusing to provision scenarios in production environments.');
	}

	protected function configureConnection(?string $connection): void
	{
		if (!$connection)
			return;

		Config::set('database.default', $connection);
		DB::purge($connection);
		DB::setDefaultConnection($connection);
	}

	protected function runFreshMigrations(?string $connection = null): void
	{
		$params = [
			'--force' => true,
		];

		if ($this->option('seed'))
			$params['--seed'] = true;

		if ($connection)
			$params['--database'] = $connection;

		Artisan::call('migrate:fresh', $params);
	}

	protected function parseDataOption(?string $raw): mixed
	{
		if ($raw === null || $raw === '')
			return null;

		if (str_starts_with($raw, '@')) {
			$path = substr($raw, 1);
			if (!is_file($path))
				throw new RuntimeException("Unable to read data file at {$path}.");

			$raw = file_get_contents($path);
		}

		try {
			return json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
		} catch (JsonException $exception) {
			throw new RuntimeException('Failed to decode --data payload: ' . $exception->getMessage(), 0, $exception);
		}
	}

	protected function shouldRefreshModels(): bool
	{
		if ($this->option('refresh-models'))
			return true;

		if ($this->option('no-refresh-models'))
			return false;

		return config('test-scenarios.provision.refresh_models', true);
	}

}
