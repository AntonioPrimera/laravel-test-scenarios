<?php
namespace AntonioPrimera\TestScenarios\Commands;

use Illuminate\Console\Command;
use RuntimeException;

class ShowDocsCommand extends Command
{
	protected $signature = 'test-scenarios:docs {--section=all : all|readme|agents}';

	protected $description = 'Display the Test Scenarios README and agent guide.';

	public function handle(): int
	{
		$section = strtolower($this->option('section') ?? 'all');

		$sections = [
			'readme' => [
				'title' => 'README',
				'path' => $this->packagePath('readme.md'),
			],
			'agents' => [
				'title' => 'Agent Guide',
				'path' => $this->packagePath('docs/agents.md'),
			],
		];

		if (!in_array($section, ['all', 'readme', 'agents'], true))
			throw new RuntimeException('Invalid section. Use all, readme, or agents.');

		foreach ($sections as $key => $meta) {
			if ($section !== 'all' && $section !== $key)
				continue;

			if (!is_file($meta['path'])) {
				$this->components->error("Unable to locate {$meta['title']} at {$meta['path']}.");
				continue;
			}

			$this->components->twoColumnDetail($meta['title'], $meta['path']);
			$this->newLine();
			$this->line(file_get_contents($meta['path']));
			$this->newLine(2);
		}

		return self::SUCCESS;
	}

	protected function packagePath(string $relative): string
	{
		return realpath(__DIR__ . '/../../' . $relative) ?: __DIR__ . '/../../' . $relative;
	}
}
