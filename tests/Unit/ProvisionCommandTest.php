<?php
namespace AntonioPrimera\TestScenarios\Tests\Unit;

use AntonioPrimera\TestScenarios\Tests\Context\SimpleScenario;
use AntonioPrimera\TestScenarios\Tests\TestCase;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class ProvisionCommandTest extends TestCase
{
    #[Test]
    public function it_confirms_provisioning()
    {
        config(['test-scenarios.aliases.blog' => SimpleScenario::class]);

        $this->artisan('test-scenarios:provision', ['scenario' => 'blog'])
            ->assertExitCode(0);
    }

    #[Test]
    public function it_refuses_to_run_in_production_environments()
    {
        $originalEnv = $this->app['env'];
        $this->app['env'] = 'production';

        try {
            $this->expectException(RuntimeException::class);

            $this->artisan('test-scenarios:provision', [
                'scenario' => SimpleScenario::class,
            ])->run();
        } finally {
            $this->app['env'] = $originalEnv;
        }
    }

    #[Test]
    public function it_runs_migrate_fresh_without_seeding_by_default()
    {
        DatabaseSeeder::reset();

        $this->artisan('test-scenarios:provision', [
            'scenario' => SimpleScenario::class,
        ])->run();

        $this->assertSame(0, DatabaseSeeder::$runCount);
    }

    #[Test]
    public function it_can_seed_when_requested()
    {
        DatabaseSeeder::reset();

        $this->artisan('test-scenarios:provision', [
            'scenario' => SimpleScenario::class,
            '--seed' => true,
        ])->run();

        $this->assertSame(1, DatabaseSeeder::$runCount);
    }
}
