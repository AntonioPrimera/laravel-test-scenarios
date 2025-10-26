<?php
namespace AntonioPrimera\TestScenarios\Tests\Unit;

use AntonioPrimera\TestScenarios\Tests\Context\AppContext;
use AntonioPrimera\TestScenarios\Tests\Context\SimpleScenario;
use AntonioPrimera\TestScenarios\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;

class DocumentationCommandsTest extends TestCase
{
    #[Test]
    public function it_outputs_agent_docs()
    {
        Artisan::call('test-scenarios:docs', ['--section' => 'agents']);

        $output = Artisan::output();

        $this->assertStringContainsString('Agent Guide', $output);
        $this->assertStringContainsString('Deterministic data', $output);
    }

    #[Test]
    public function it_lists_registered_scenarios_with_descriptions()
    {
        config(['test-scenarios.aliases.demo' => SimpleScenario::class]);

        Artisan::call('test-scenarios:list', ['--json' => true]);
        $payload = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('demo', $payload[0]['alias']);
        $this->assertSame(SimpleScenario::class, $payload[0]['class']);
        $this->assertStringContainsString('Seeds a single product', $payload[0]['description']);
    }

    #[Test]
    public function it_lists_registered_contexts_and_builder_methods()
    {
        config(['test-scenarios.contexts' => [AppContext::class]]);

        Artisan::call('test-contexts:list', ['--json' => true]);
        $payload = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame(AppContext::class, $payload[0]['class']);
        $this->assertContains('createProduct', $payload[0]['builders']);
        $this->assertContains('createComment', $payload[0]['builders']);
    }
}
