<?php

namespace uTech\Tests\Gestao\Feriados\PageObject;

use Facebook\WebDriver\WebDriverBy;
use uTech\Tests\Commons\PageObject\ManagementPageObject;


class FeriadosPageObject extends ManagementPageObject
{
    public function countTableItems(int $bodyPos = 0): int
    {
        return count(self::getTableRows($bodyPos));
    }

    public function getFeriadoTableIndex(string $name): int
    {
        $lines = self::getTableRows();
        foreach ($lines as $index => $line) {
            if (str_contains($line->getText(), $name)) {
                return $index;
            }
        }
        return -1;
    }

    public function clickButtonByType(string $typeName):void
    {
        self::clickButton("@type", $typeName);
    }
}
