<?php

namespace uTech\Tests\Gestao\Feriados\PageObject;

use Exception;
use Facebook\WebDriver\WebDriverExpectedCondition;
use uTech\Tests\Commons\PageObject\ManagementPageObject;

class FeriadosAddPageObject extends ManagementPageObject
{

    public function getFieldValueByName(string $name): string
    {
        return self::getFieldValueByXpath("@name", $name);
    }

    public function getFieldValueByXpath(string $property, string $propertyValue): string
    {
        $element = self::getElementByXpath($property, $propertyValue);
        if (!$element) {
            throw new Exception('Elemento não encontrado');
        }
        return $element->getAttribute("value");
    }

    public function acceptAlert(): void
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::alertIsPresent());
        $this->driver->switchTo()->alert()->accept();
    }
}
