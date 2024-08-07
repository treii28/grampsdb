<?php

namespace Tests;

if(!trait_exists("Tests\\CreatesApplication"))
    require_once(__DIR__."/CreatesApplication.php");

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;

if(!class_exists("Tests\\TestCase")) {
    abstract class TestCase extends BaseTestCase
    {
        use CreatesApplication;

        /**
         * @var \Illuminate\Foundation\Application $app
         */
        protected $app;

        /**
         * @return void
         * @throws \Exception
         */
        public function setUp(): void
        {
            $this->app = $this->createApplication();
            parent::setUp();
        }
    }
}
