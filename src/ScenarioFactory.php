<?php
namespace AntonioPrimera\TestScenarios;

use Illuminate\Contracts\Config\Repository;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ScenarioFactory
{
	public function __construct(
		protected Repository $config
	) {}

	public function make(string $aliasOrClass, mixed $setupData = null, ?TestCase $testCase = null): TestScenario
	{
		$class = $this->resolveClass($aliasOrClass);

		if (!is_subclass_of($class, TestScenario::class))
			throw new InvalidArgumentException("Class {$class} must extend " . TestScenario::class . '.');

		return new $class($testCase, $setupData);
	}

	public function resolveClass(string $aliasOrClass): string
	{
		$aliases = $this->config->get('test-scenarios.aliases', []);

		if (isset($aliases[$aliasOrClass]))
			return $aliases[$aliasOrClass];

		if (!class_exists($aliasOrClass))
			throw new InvalidArgumentException("Test scenario class {$aliasOrClass} was not found.");

		return $aliasOrClass;
	}
}
