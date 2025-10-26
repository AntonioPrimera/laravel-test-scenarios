<?php
namespace AntonioPrimera\TestScenarios\Commands;

use AntonioPrimera\TestScenarios\Support\DocComment;
use AntonioPrimera\TestScenarios\TestContext;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use ReflectionClass;
use ReflectionMethod;

class ListTestContexts extends Command
{
	protected $signature = 'test-contexts:list {--json : Output JSON instead of a table}';

	protected $description = 'List registered test contexts and their builder methods.';

	public function __construct(protected Repository $config)
	{
		parent::__construct();
	}

	public function handle(): int
	{
		$contexts = $this->config->get('test-scenarios.contexts', []);

		if (empty($contexts)) {
			$this->components->info('No test contexts registered. Update config/test-scenarios.php.');
			return self::SUCCESS;
		}

		$rows = [];

		foreach ($contexts as $contextClass) {
			if (!class_exists($contextClass)) {
				$rows[] = [
					'class' => $contextClass,
					'description' => 'Class not found',
					'builders' => [],
				];
				continue;
			}

			$reflection = new ReflectionClass($contextClass);

			if (!$reflection->isSubclassOf(TestContext::class)) {
				$rows[] = [
					'class' => $contextClass,
					'description' => 'Does not extend ' . TestContext::class,
					'builders' => [],
				];
				continue;
			}

			$description = DocComment::summary($reflection->getDocComment()) ?? '—';

			$builderMethods = [];

			foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
				if ($method->getDeclaringClass()->getName() !== $contextClass)
					continue;

				if ($method->isConstructor() || in_array($method->getName(), ['setTestCase'], true))
					continue;

				$builderMethods[] = $method->getName();
			}

			$rows[] = [
				'class' => $contextClass,
				'description' => $description,
				'builders' => $builderMethods,
			];
		}

		if ($this->option('json')) {
			$this->line(json_encode($rows, JSON_PRETTY_PRINT));
		} else {
			$this->table(
				['Context', 'Description', 'Builder Methods'],
				array_map(
					fn($row) => [
						$row['class'],
						$row['description'],
						empty($row['builders']) ? '—' : implode(', ', $row['builders']),
					],
					$rows
				)
			);
		}

		return self::SUCCESS;
	}
}
