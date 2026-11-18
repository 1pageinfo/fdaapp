<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Safety guard: tests wipe their database (RefreshDatabase), so they must only
     * ever run on the in-memory SQLite database from phpunit.xml. If the config is
     * cached (php artisan config:cache), Laravel ignores phpunit.xml and would use
     * the real MySQL database — stop before anything is touched.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$connection}.database");
        if ($connection !== 'sqlite' || $database !== ':memory:') {
            fwrite(STDERR, "\n\nREFUSING TO RUN TESTS: they would use the '{$connection}' database '{$database}' "
                . "instead of in-memory SQLite. Run `php artisan config:clear` first.\n\n");
            exit(1);
        }

        return $app;
    }
}
