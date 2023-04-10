<?php

namespace uTech\Tests\Commons\Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use PHPUnit\Framework\TestCase;

class PBXTestCase extends TestCase
{
    public function getCapabilitiesChrome(string $visibility): DesiredCapabilities
    {
        $capabilities = DesiredCapabilities::chrome();
        $options = new ChromeOptions();
        $options->addArguments(['headless']);
        return ($visibility) ? $capabilities : $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
    }

    public function getCapabilitiesFirefox(string $visibility): DesiredCapabilities
    {
        $capabilities = DesiredCapabilities::firefox();
        $options = new FirefoxOptions();
        $options->addArguments(['headless']);
        return ($visibility) ? $capabilities : $capabilities->setCapability(FirefoxOptions::CAPABILITY, $options);
    }

    public function getCapabilities(string $browser, string $visibility): DesiredCapabilities
    {
        return ($browser === "firefox") ? self::getCapabilitiesFirefox($visibility) : self::getCapabilitiesChrome($visibility);
    }
}
