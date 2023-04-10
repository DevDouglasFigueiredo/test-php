<?php

namespace uTech\Tests\Commons\PageObject;

use Exception;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;

include_once "tests/Commons/Constants/CommonsConstants.php";
include_once 'config.inc';

class ManagementPageObject
{
    protected WebDriver $driver;
    protected string $ip;
    protected string $url;

    public function __construct(WebDriver $driver, string $url)
    {
        global $ip;
        $this->ip = $ip;
        $this->url = $url;
        $this->driver = $driver;
    }

    public function visit(): void
    {
        $this->driver->manage()->window()->maximize();
        $this->driver->get( HTTP . $this->ip . BASE_URL . $this->url);
    }

    public function clickButtonByTexts(array $text, int $buttonPos = 0): void
    {
        $elements = self::getElementsByTag("button");
        $result = [];
        foreach ($elements as $element) {
            if (in_array($element->getText(), $text)) {
                array_push($result, $element);
            }
        }
        self::clickElement($result[$buttonPos]);
    }

    public function clickButtonWithText(string $text, int $buttonPos = 0): void
    {
        self::clickButtonByTexts(array($text),$buttonPos);
    }

    public function clickButtonByLinkText(string $text, int $position = 0): void
    {
        $element = $this->driver->findElements(WebDriverBy::LinkText($text));
        self::clickElement($element[$position]);
    }

    public function clearAndSetFieldByName(string $name, string $text): void
    {
        self::clearAndSetField("@name", $name, $text);
    }

    public function clickButtonByClass(string $className): void
    {
        self::clickButton("@class", $className);
    }

    public function clickButtonByName(string $name): void
    {
        self::clickButton("@name", $name);
    }

    public function clickButtonById(string $name): void
    {
        self::clickButton("@id", $name);
    }

    public function clickButton(string $property, string $propertyValue): void
    {
        $element = self::getElementByXpath($property, $propertyValue);
        self::clickElement($element);
    }

    public function clickElement(WebDriverElement $element): void
    {
        if (!$element) {
            throw new Exception("Elemento Nulo"); 
        }
        if (!($element->isDisplayed() && $element->isEnabled())) {
            throw new Exception("Elemento não interativo");
        }
        $element->click();
    }

    public function clearAndSetField(string $property, string $propertyValue, string $value): void
    {
        $element = self::getElementByXpath($property, $propertyValue);
        if(!$element)
        {
            throw new Exception("Elemento não encontrado");
        }
        $element->clear()->sendKeys($value);
    }

    public function getElementByXpath(string $property, string $propertyValue, $pos = 0): WebDriverElement
    {
        $elements = self::getElementsByXpath($property, $propertyValue);
        return $elements[$pos] ? $elements[$pos] : NULL;
    }

    public function getElementsByXpath(string $property, string $propertyValue): array
    {
        return $this->driver->findElements(self::getWebDriverByXpath($property, $propertyValue));
    }

    public function getWebDriverByXpath(string $property, string $propertyValue): WebDriverBy
    {
        return WebDriverBy::xpath("//*[" . $property . "='" . $propertyValue . "']");
    }

    public function getElementsByTag(string $tagName): array
    {
        return $this->driver->findElements(WebDriverBy::tagName($tagName));
    }

    public function getTableRows(int $bodyPos = 0): array
    {
        $tablesBody = self::getElementsByTag("tbody");
        return (!$tablesBody[$bodyPos]) ? [] : $tablesBody[$bodyPos]->findElements(WebDriverBy::tagName("tr"));
    }
}