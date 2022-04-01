<?php
namespace AntonioPrimera\TestScenarios\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

trait HandlesAuthentication
{
	public function login($actor): static
    {
        if (!$this->testCase)
            throw new \Exception('No TestCase instance available - instantiate the TestContext, by providing the TestCase instance to its constructor');
        
        $actorInstance = $this->getInstance(Authenticatable::class, $actor, true);
        /* @var Authenticatable $actorInstance */
		
        $this->testCase->actingAs($actorInstance);
        
        return $this;
    }
    
    public function logout(): static
    {
        if (!$this->testCase)
            throw new \Exception('No test case instance available - instantiate the TestContext, by providing the TestCase instance to its constructor');
        
        if (is_callable([$this->testCase, 'logout'])) {
            $this->testCase->logout();
            return $this;
        }
        
        Auth::logout();
        
        return $this;
    }
}