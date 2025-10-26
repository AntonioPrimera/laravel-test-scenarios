<?php
namespace AntonioPrimera\TestScenarios;

use AntonioPrimera\TestScenarios\Commands\GenerateTestContext;
use AntonioPrimera\TestScenarios\Commands\GenerateTestScenario;
use AntonioPrimera\TestScenarios\Commands\ListScenarios;
use AntonioPrimera\TestScenarios\Commands\ListTestContexts;
use AntonioPrimera\TestScenarios\Commands\ProvisionScenario;
use AntonioPrimera\TestScenarios\Commands\ShowDocsCommand;
use AntonioPrimera\TestScenarios\ScenarioFactory;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;

class TestScenarioServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->mergeConfigFrom(__DIR__ . '/../config/test-scenarios.php', 'test-scenarios');

		$this->app->singleton(ScenarioFactory::class, function ($app) {
			return new ScenarioFactory($app->make(Repository::class));
		});
	}

	public function boot()
	{
		$this->publishes([
			__DIR__ . '/../config/test-scenarios.php' => function_exists('config_path')
				? config_path('test-scenarios.php')
				: base_path('config/test-scenarios.php'),
		], 'test-scenarios-config');

		if ($this->app->runningInConsole()) {
			$this->commands([
				GenerateTestContext::class,
				GenerateTestScenario::class,
				ListScenarios::class,
				ListTestContexts::class,
				ProvisionScenario::class,
				ShowDocsCommand::class,
			]);
		}
	}
}
