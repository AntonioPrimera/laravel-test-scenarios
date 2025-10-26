<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Scenario Aliases
    |--------------------------------------------------------------------------
    |
    | Define short aliases for your scenarios so you can address them via the
    | provisioning command and JS helpers without typing the full class name.
    |
    */
    'aliases' => [
        // 'blog' => Tests\Scenarios\BlogScenario::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Context Registry
    |--------------------------------------------------------------------------
    |
    | Register your TestContext classes here so the `test-contexts:list` command
    | can describe the available builder methods to other developers/agents.
    |
    */
    'contexts' => [
        // Tests\Context\AppContext::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Provisioning Defaults
    |--------------------------------------------------------------------------
    |
    | These options control the default behaviour of the test-scenarios:provision
    | command. They can be overridden via CLI flags / helper options.
    |
    */
    'provision' => [
        'refresh_models' => true,
    ],
];
