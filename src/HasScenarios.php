<?php

namespace AntonioPrimera\TestScenarios;

use Illuminate\Support\Collection;

trait HasScenarios
{
	/**
	 * Call this method in your root TestCase to set up all typed scenario attributes.
	 * This will instantiate all properties which have as type a class inheriting
	 * the TestScenario class, so you don't have to do it for every test.
	 */
	protected function setupScenarios()
	{
		$rc = new \ReflectionClass(static::class);
		
		//get all properties which have a type inheriting the TestScenario abstract class
		$scenarioProperties = Collection::wrap($rc->getProperties())
			->filter(
				fn(\ReflectionProperty $property) =>
					($type = $property->getType())
					&& is_subclass_of("\\$type", TestScenario::class));
		
		//now instantiate all properties
		foreach ($scenarioProperties as $scenarioProperty) {
			/* @var \ReflectionProperty $scenarioProperty */
			$name = $scenarioProperty->getName();
			$class = '\\' . $scenarioProperty->getType();
			
			$this->$name = new $class($this);
		}
	}
}