<?php

namespace AntonioPrimera\TestScenarios\Tests\Context;

use AntonioPrimera\TestScenarios\TestContext;
use AntonioPrimera\TestScenarios\Tests\Context\Traits\CreatesTestComments;
use AntonioPrimera\TestScenarios\Tests\Context\Traits\CreatesTestProducts;

class AppContext extends TestContext
{
	//add all traits to your local test context
	use CreatesTestComments,
		CreatesTestProducts;
}