<?php

namespace AntonioPrimera\TestScenarios\Tests\Context\Traits;

use AntonioPrimera\TestScenarios\Tests\Context\TestModels\Product;

trait CreatesTestProducts
{
	
	public function createProduct(string $attribute, array $data = [])
	{
		$product = new Product($data);
		return $this->set($attribute, $product);
	}
}