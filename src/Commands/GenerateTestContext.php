<?php
namespace AntonioPrimera\TestScenarios\Commands;

use AntonioPrimera\Artisan\FileGeneratorCommand;
use AntonioPrimera\Artisan\FileRecipe;

class GenerateTestContext extends FileGeneratorCommand
{
	protected $signature = "tests:create-context {name=TestContext}";
	protected $description = "This command will generate a Test Context Class for your application in tests/Context";
	
	protected function recipe(): array
	{
		//$contextClassRecipe = new FileRecipe(__DIR__ . '/stubs/ContextClass.php.stub', 'tests/Context');
		//$contextClassRecipe->rootNamespace = 'Tests\\Context';
		//$contextClassRecipe->rootPath = 'base_path';
		//$contextClassRecipe->replace = [
		//	'MODEL_FACTORY_METHODS' => $this->generateFactoryMethodsForModels(),
		//	'MODEL_IMPORTS' => $this->generateModelImports(),
		//];
		
		return [
			'Context Class' => FileRecipe::create(
				stub: __DIR__ . '/stubs/ContextClass.php.stub',
				target: 'tests/Context',
				rootNamespace: 'Tests\\Context',
				replace: [
					'MODEL_FACTORY_METHODS' => $this->generateFactoryMethodsForModels(),
					'MODEL_IMPORTS' => $this->generateModelImports(),
				]
			)
		];
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function generateFactoryMethodsForModels(): string
	{
		//determine all project models (from app/Models) and get their base names
		$modelBaseNames = $this->getModelNames();
		
		//generate a factory method for each model: "create{ModelName}(string $key, $parentPost, $author, $data = [])"
		return $modelBaseNames
			->map(fn ($modelName) => $this->factoryMethodTemplate($modelName))
			->implode("\n");
	}
	
	protected function generateModelImports(): string
	{
		return $this->getModelNames()->map(fn($modelBaseName) => "use App\\Models\\{$modelBaseName};")->implode("\n");
	}
	
	protected function factoryMethodTemplate(string $modelClass): string
	{
		$modelVariable = lcfirst($modelClass);
		return "\tpublic function create{$modelClass}(string \$key = null, array \$data = []): {$modelClass} \n"
			. "\t{\n"
			. "\t\t\${$modelVariable} = {$modelClass}::factory()->create(array_merge(\$data, []));\n"
			. "\t\treturn \$this->set(\$key, \${$modelVariable});\n"
			. "\t}\n";
	}
	
	protected function getModelNames()
	{
		//determine all project models (from app/Models) and get their base names
		return collect(scandir(app_path('Models')))
			->filter(fn($file) => !in_array($file, ['.', '..']))
			->filter(fn($file) => is_file(app_path("Models/$file")) && str_ends_with($file, '.php'))
			->map(fn($file) => str_replace('.php', '', $file))
			->filter(fn($file) => is_subclass_of("App\\Models\\$file", 'Illuminate\\Database\\Eloquent\\Model'));
	}
}