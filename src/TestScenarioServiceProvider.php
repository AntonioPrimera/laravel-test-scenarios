<?php
namespace AntonioPrimera\TestScenarios;

use AntonioPrimera\TestScenarios\Commands\GenerateTestContext;
use AntonioPrimera\TestScenarios\Commands\GenerateTestScenario;
use Illuminate\Support\ServiceProvider;

class TestScenarioServiceProvider extends ServiceProvider
{
	
	public function boot()
	{
		if ($this->app->runningInConsole()) {
			$this->commands([
				GenerateTestContext::class,
				GenerateTestScenario::class,
			]);
		}
	}
}