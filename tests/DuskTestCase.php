<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseMigrations;

    protected function baseUrl(): string
    {
        return 'http://nginx';
    }

    protected function driver(): RemoteWebDriver
    {
        $options = new ChromeOptions();

        // 👇 NO headless here
        $options->addArguments([
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ]);

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

        return RemoteWebDriver::create(
            'http://selenium:4444/wd/hub',  // selenium service from docker-compose
            $capabilities
        );
    }
    
}
