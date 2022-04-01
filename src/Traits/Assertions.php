<?php

namespace AntonioPrimera\TestScenarios\Traits;

trait Assertions
{
	
	public function assertInstanceOf($className, $attributeOrModel)
	{
		$object = is_string($attributeOrModel) ? $this->$attributeOrModel : $attributeOrModel;
		$this->testCase->assertInstanceOf($className, $object);
	}
	
	public function assertHas($className, $attribute)
	{
		$this->testCase->assertInstanceOf($className, $this->$attribute);
	}
}