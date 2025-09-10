<?php
namespace AntonioPrimera\TestScenarios\Commands;

use AntonioPrimera\Artisan\FileGeneratorCommand;
use AntonioPrimera\Artisan\FileRecipe;

class GenerateTestScenario extends FileGeneratorCommand
{
	protected $signature = "tests:create-scenario {name}";
	protected $description = "Generate a Test Scenario Class in tests/Scenarios";
	
	protected function recipe(): array
	{
		//$scenarioClassRecipe = new FileRecipe(__DIR__ . '/stubs/ScenarioClass.php.stub', 'tests/Scenarios');
		//$scenarioClassRecipe->rootNamespace = 'Tests\\Scenarios';
		//$scenarioClassRecipe->rootPath = 'base_path';
		//$scenarioClassRecipe->replace = [
		//	'CONTEXT_CLASS' => $this->getContextClass(),
		//];
		
		return [
			'Scenario Class' => FileRecipe::create(
				stub: __DIR__ . '/stubs/ScenarioClass.php.stub',
				targetFolder: 'tests/Scenarios',
				rootNamespace: 'Tests\\Scenarios',
				replace: [
					'CONTEXT_CLASS' => $this->getContextClass(),
				]
			)
		];
	}
	
	protected function getContextClass(): string
	{
		if (!is_dir(base_path('tests/Context'))) {
			$this->warn("No context class found in tests/Context, using default TestContext. Create a context class using the 'php artisan make:test-context' command.");
			return 'TestContext';
		}
		
		//check if there is a context class in tests/Context
		$contextClass = collect(scandir(base_path('tests/Context')))
			->filter(fn($file) => is_file(base_path("tests/Context/$file")) && str_ends_with($file, '.php'))
			->map(fn($file) => substr($file, 0, -4))
			->first();
		
		if (!$contextClass) {
			$this->warn("No valid context class found in tests/Context, using default TestContext. Create a context class using the 'php artisan make:test-context' command.");
			return 'TestContext';
		}
		
		return $contextClass;
	}
}