<?php

namespace uTech\Tests\Login\PageObject;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use uTech\Tests\Commons\PageObject\ManagementPageObject;

class LoginPageObject extends ManagementPageObject
{
    function getSectionText(string $section): string
    {
        $elements = self::getElementsByTag("$section");
        if (!$elements[0]) {
            throw new Exception("Sessão inexistente");
        }
        return $elements[0]->getText();
    }

    function waitPresenceOfElementLocatedByName(string $elementName): void
    {
        $property = "@name";
        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath("//*[" . $property . "='" . $elementName . "']")));
    }
}
