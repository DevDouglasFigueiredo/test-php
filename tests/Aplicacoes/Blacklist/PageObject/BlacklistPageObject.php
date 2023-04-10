<?php

namespace uTech\Tests\Aplicacoes\Blacklist\PageObject;

use Exception;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

use uTech\Tests\Commons\PageObject\ManagementPageObject;

include_once "tests/Commons/Constants/CommonsConstants.php";
include_once "tests/Aplicacoes/Blacklist/Constants/BlacklistConstants.php";

class BlacklistPageObject extends ManagementPageObject
{
    public function countTableItems(): int
    {
        $table = self::getTableRows(0);
        if(count($table) === 0) 
        {
            return 0;
        }
        $firstLine = $table[0];
        return ($firstLine->getText() === "Nenhum registro encontrado") ? 0 : count($table);
    }

    public function getBlacklistTableIndex(string $name): int
    {
        $lines = self::getTableRows();
        foreach ($lines as $index => $line) {
            if (str_contains($line->getText(), $name)) {
                return $index;
            }
        }
        return -1;
    }
 
    public function clickButtonByExecuteScriptById(string $id): void
    {
        $this->driver->executeScript('document.getElementById("' . $id . '").click()');
    }

    public function getElementById(string $idElement): WebDriverElement
    {
        $element = self::getElementByXpath("@id", $idElement);
        if (!$element) {
            throw new Exception("Elemento Nulo");
        }
        return $element;
    }

    public function getFieldValueById(string $id): string
    {
        return self::getFieldValueByXpath("@id", $id);
    }

    public function getFieldValueByXpath(string $property, string $propertyValue): string
    {
        $element = self::getElementByXpath($property, $propertyValue);
        if (!$element) {
            throw new Exception('Elemento não encontrado');
        }
        return $element->getAttribute("value");
    }

    public function clearAndSetFieldByClass(string $name, string $text): void
    {
        self::clearAndSetField("@class", $name, $text);
    }

    public function setFilePathById(string $id, string $path): void
    {
        $fileInput = self::getElementByXpath("@id", $id);
        $fileInput->setFileDetector(new LocalFileDetector());
        $fileInput->sendKeys($path);
    }

    private function getWebDriverSelect(string $property, string $propertyValue, $position = 0): WebDriverSelect
    {
        $element = self::getElementByXpath($property, $propertyValue, $position);
        $selector = new WebDriverSelect($element);
        return $selector ? $selector : NULL;
    }

    public function getWebDriverSelectByName(string $selectorName): WebDriverSelect
    {
        $selector = self::getWebDriverSelect("@name", $selectorName);
        if (!$selector) {
            throw new Exception("Elemento não encontrado");
        }
        return $selector;
    }

    public function selectSelectorByValue(WebDriverSelect $selector, string $value): void
    {
        $selector->selectByValue($value);
    }

    public function selectSelectorByIndex(WebDriverSelect $selector, int $index): void
    {
        $selector->selectByIndex($index - 1);
    }

    public function getSelectedItemFromSelector(WebDriverSelect $selector): string
    {
        return trim($selector->getFirstSelectedOption()->getText());
    }

    public function getValueSelectedItemFromSelector(WebDriverSelect $selector): string
    {
        return trim($selector->getFirstSelectedOption()->getAttribute("value"));
    }

    public function waitVisibilityOfElementLocatedByClass(string $classTable): void
    {
        $webDriver = self::getWebDriverByXpath("@class", $classTable);
        $this->driver->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated($webDriver));
    }

    public function waitElementTextContainsByClass(string $classTable, string $title): void
    {
        $webDriver = self::getWebDriverByXpath("@class", $classTable);
        $this->driver->wait(10)->until(WebDriverExpectedCondition::elementTextContains($webDriver, $title));
    }

    public function waitElementTextNotContainsByClass(string $classTable, string $title): void
    {
        $webDriver = self::getWebDriverByXpath("@class", $classTable);
        $this->driver->wait(10)->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::elementTextContains($webDriver, $title)));
    }
   
    public function acceptAlert(): void
    {
        $this->driver->wait()->until(WebDriverExpectedCondition::alertIsPresent());
        $this->driver->switchTo()->alert()->accept();
    }
}