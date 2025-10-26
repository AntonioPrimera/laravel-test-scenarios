<?php
namespace AntonioPrimera\TestScenarios\Commands;

use AntonioPrimera\TestScenarios\Support\DocComment;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;

class ListScenarios extends Command
{
	protected $signature = 'test-scenarios:list {--json : Output JSON instead of a table}';

	protected $description = 'List registered test scenarios and their descriptions.';

	public function __construct(protected Repository $config)
	{
		parent::__construct();
	}

	public function handle(): int
	{
		$aliases = $this->config->get('test-scenarios.aliases', []);

		if (empty($aliases)) {
			$this->components->info('No scenario aliases registered. Update config/test-scenarios.php.');
			return self::SUCCESS;
		}

		$rows = [];

		foreach ($aliases as $alias => $class) {
			$description = class_exists($class)
				? (DocComment::summary((new \ReflectionClass($class))->getDocComment()) ?? '—')
				: 'Class not found';

			$rows[] = [
				'alias' => $alias,
				'class' => $class,
				'description' => $description,
			];
		}

		if ($this->option('json')) {
			$this->line(json_encode($rows, JSON_PRETTY_PRINT));
		} else {
			$this->table(['Alias', 'Class', 'Description'], array_map(
				fn($row) => [$row['alias'], $row['class'], $row['description']],
				$rows
			));
		}

		return self::SUCCESS;
	}
}
