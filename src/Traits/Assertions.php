<?php

namespace AntonioPrimera\TestScenarios\Traits;

trait Assertions
{
	
	public function assertInstanceOf($className, $attributeOrModel)
	{
		$object = is_string($attributeOrModel) ? $this->$attributeOrModel : $attributeOrModel;
		$this->requireTestCase(__FUNCTION__)->assertInstanceOf($className, $object);
	}
	
	public function assertHas($className, $attribute)
	{
		$this->requireTestCase(__FUNCTION__)->assertInstanceOf($className, $this->$attribute);
	}
}
