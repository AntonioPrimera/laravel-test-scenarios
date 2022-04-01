<?php
namespace AntonioPrimera\TestScenarios;

use PHPUnit\Framework\TestCase;
use AntonioPrimera\TestScenarios\Traits\HandlesAttributes;
use AntonioPrimera\TestScenarios\Traits\HandlesAuthentication;

class TestContext
{
    use HandlesAttributes,
        HandlesAuthentication;

    protected ?TestCase $testCase;

    public function __construct(?TestCase $testCase = null)
    {
        $this->testCase = $testCase;
    }

    /**
     * Setting the TestCase instance after construction, enables
     * us to gain access to TestCase methods (e.g. "login")
     */
    public function setTestCase(TestCase $testCase): static
    {
        $this->testCase = $testCase;

        return $this;
    }
}
