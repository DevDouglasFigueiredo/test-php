<?php

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use uTech\Tests\Aplicacoes\Blacklist\PageObject\BlacklistPageObject;
use uTech\Tests\Commons\Tests\PBXTestCase;
use uTech\Tests\Login\Functions\LoginFunctions;

include_once 'config.inc';
include_once "tests/Commons/Constants/CommonsConstants.php";
include_once "tests/Aplicacoes/Blacklist/Constants/BlacklistConstants.php";

class BlacklistTest extends PBXTestCase
{
    private static WebDriver $driver;
    private static BlacklistPageObject $paginaBlacklist;

    public static function setUpBeforeClass(): void
    {
        global $user, $pass, $driverHost, $browser, $visibleTest;
        self::$driver = RemoteWebDriver::create($driverHost, self::getCapabilities($browser, $visibleTest));

        $loginFunctions = new LoginFunctions(self::$driver);
        $loginFunctions->login($user, $pass);
    }

    public function setUp(): void
    {
        self::$paginaBlacklist = new BlacklistPageObject(self::$driver, URL_BLACKLIST_PAGE);
    }

    public function testAdicionarBlacklistSemDados(): void
    {
        self::$paginaBlacklist->visit();
        $initialNumberOfBlacklistItems = self::$paginaBlacklist->countTableItems();
        self::addBlacklist(['number' => "", 'desc' => ""]);
        self::$paginaBlacklist->acceptAlert();
        self::$paginaBlacklist->clickButtonByTexts([CLOSE, SAIR]);

        self::assertSame($initialNumberOfBlacklistItems, self::$paginaBlacklist->countTableItems(), "Não deveria ter sido adicionado um item na Blacklist");
    }

    /**
     * @dataProvider entranceAdicionarEEditarBlacklist
     */
    public function testAdicionarBlacklist(string $number, string $desc): void
    {
        self::$paginaBlacklist->visit();
        $initialNumberOfBlacklistItems = self::$paginaBlacklist->countTableItems();

        self::addBlacklist(['number' => $number, 'desc' => $desc]);
        self::$paginaBlacklist->waitElementTextContainsByClass(TABLE_CLASS, $number);
        self::assertSame($initialNumberOfBlacklistItems, self::$paginaBlacklist->countTableItems() - 1, "Deveria ter sido adicionado um item na Blacklist");

        self::$paginaBlacklist->clickButtonByLinkText(BT_EDIT, self::$paginaBlacklist->getBlacklistTableIndex($number));
        self::assertSame($number, self::$paginaBlacklist->getFieldValueById(FIELD_NUMERO_ID), "O valor do campo 'Número' não é o esperado, o esperado seria $number");
        self::assertSame($desc, self::$paginaBlacklist->getFieldValueById(FIELD_DESCRICAO_ID), "O valor do campo 'Descrição' não é o esperado, o esperado seria $desc");
        self::$paginaBlacklist->clickButtonByTexts([CLOSE, SAIR]);

        self::removeBlacklistByText($number);

        self::$paginaBlacklist->waitElementTextNotContainsByClass(TABLE_CLASS, $number);
        self::assertSame($initialNumberOfBlacklistItems, self::$paginaBlacklist->countTableItems(), "O item não foi removido da Blacklist");
    }

    /**
     * @dataProvider entranceAdicionarEEditarBlacklist
     */
    public function testEditarBlacklist(string $number, string $desc)
    {
        self::$paginaBlacklist->visit();
        $initialNumberOfBlacklistItems = self::$paginaBlacklist->countTableItems();

        self::addBlacklist(['number' => "48123456789", 'desc' => "Provisório"]);
        self::$paginaBlacklist->waitElementTextContainsByClass(TABLE_CLASS, "48123456789");
        self::assertSame($initialNumberOfBlacklistItems, self::$paginaBlacklist->countTableItems() - 1, "Deveria ter sido adicionado um item na Blacklist");

        self::$paginaBlacklist->clickButtonByLinkText(BT_EDIT, self::$paginaBlacklist->getBlacklistTableIndex("48123456789"));
        self::fillFields(['number' => $number, 'desc' => $desc]);
        self::$paginaBlacklist->clickButtonById(BT_SALVAR);

        self::$paginaBlacklist->waitElementTextContainsByClass(TABLE_CLASS, $number);
        self::$paginaBlacklist->clickButtonByLinkText(BT_EDIT, self::$paginaBlacklist->getBlacklistTableIndex($number));
        self::assertSame($number, self::$paginaBlacklist->getFieldValueById(FIELD_NUMERO_ID), "O valor do campo 'Número' não é o esperado, o esperado seria $number");
        self::assertSame($desc, self::$paginaBlacklist->getFieldValueById(FIELD_DESCRICAO_ID), "O valor do campo 'Descrição' não é o esperado, o esperado seria $desc");
        self::$paginaBlacklist->clickButtonByTexts([CLOSE, SAIR]);

        self::removeBlacklistByText($number);

        self::$paginaBlacklist->waitElementTextNotContainsByClass(TABLE_CLASS, $number);
        self::assertSame($initialNumberOfBlacklistItems, self::$paginaBlacklist->countTableItems(), "O item não foi removido da Blacklist");
    }

    public function testPesquisarBlacklist()
    {
        self::$paginaBlacklist->visit();
        $initialNumberOfBlacklistItems = self::$paginaBlacklist->countTableItems();

        self::addBlacklist(['number' => "48995674222", 'desc' => ""]);
        self::addBlacklist(['number' => "48983202333", 'desc' => ""]);

        self::$paginaBlacklist->visit();
        self::$paginaBlacklist->waitVisibilityOfElementLocatedByClass(TABLE_CLASS);

        $expectedNumberOfBlacklist = $initialNumberOfBlacklistItems + 2;
        $expectedFilterResultCount = 1;

        self::$paginaBlacklist->clearAndSetFieldByClass(FIELD_PESQUISA_CLASS, "48995674222");
        self::$paginaBlacklist->waitElementTextNotContainsByClass(TABLE_CLASS, "48983202333");

        $filterResultCount = self::$paginaBlacklist->countTableItems();

        self::$paginaBlacklist->clearAndSetFieldByClass(FIELD_PESQUISA_CLASS, " ");
        self::$paginaBlacklist->waitElementTextContainsByClass(TABLE_CLASS, "48983202333");

        $currentNumberOfBlacklist = self::$paginaBlacklist->countTableItems();

        self::assertSame($expectedFilterResultCount, $filterResultCount, "Foi encontrado um número de itens na Blacklist diferente do esperado, era esperado $expectedFilterResultCount e foram encontrados $filterResultCount");
        self::assertSame($expectedNumberOfBlacklist, $currentNumberOfBlacklist, "O filtro não limpou corretamente os resultados");

        self::removeBlacklistByText("48995674222");
        self::removeBlacklistByText("48983202333");
        self::$paginaBlacklist->waitElementTextNotContainsByClass(TABLE_CLASS, "48983202333");
        self::assertSame($initialNumberOfBlacklistItems, self::$paginaBlacklist->countTableItems(), "O item não foi removido da Blacklist");
    }

    public function testBlacklistConfiguracoes()
    {
        self::$paginaBlacklist->visit();
        self::$paginaBlacklist->clickButtonByLinkText(CONFIGURACOES);
        self::$paginaBlacklist->clickButtonByExecuteScriptById(BT_BLOQUEAR_CHAMADOR_DESCONHECIDO_NAO);
        $destinoSelector = self::$paginaBlacklist->getWebDriverSelectByName(SELECTOR_DESTINO);
        self::$paginaBlacklist->selectSelectorByValue($destinoSelector, "Finalizar_chamadas");
       
        $optionDestinoSelectorName = self::$paginaBlacklist->getValueSelectedItemFromSelector($destinoSelector) . "0";
        $optionDestinoSelector = self::$paginaBlacklist->getWebDriverSelectByName($optionDestinoSelectorName);
        self::$paginaBlacklist->selectSelectorByValue($optionDestinoSelector, "app-blackhole,busy,1");
        self::$paginaBlacklist->clickButtonById(BT_ENVIAR);
        self::$paginaBlacklist->clickButtonByLinkText(BLACKLIST);

        self::$paginaBlacklist->clickButtonByLinkText(CONFIGURACOES);
        self::assertSame(false, self::$paginaBlacklist->getElementById(BT_BLOQUEAR_CHAMADOR_DESCONHECIDO_SIM)->isSelected(), "É esperado que o botão Não (Bloquear Chamador Desconhecido) estejá selecionado");
        self::assertSame(true, self::$paginaBlacklist->getElementById(BT_BLOQUEAR_CHAMADOR_DESCONHECIDO_NAO)->isSelected(), "É esperado que o botão Não (Bloquear Chamador Desconhecido) estejá selecionado");
        $destinoSelector = self::$paginaBlacklist->getWebDriverSelectByName(SELECTOR_DESTINO);
        self::assertSame("Finalizar chamadas", self::$paginaBlacklist->getSelectedItemFromSelector($destinoSelector), 'O Esperado é que o seletor Destino estivesse setado o valor "Finalizar chamadas"');
        $optionDestinoSelectorName = self::$paginaBlacklist->getValueSelectedItemFromSelector($destinoSelector) . "0";
        $optionDestinoSelector = self::$paginaBlacklist->getWebDriverSelectByName($optionDestinoSelectorName);
        self::assertSame("Ocupado", self::$paginaBlacklist->getSelectedItemFromSelector($optionDestinoSelector), 'O Esperado é que o seletor opção de Destino estivesse setado o valor "Ocupado"');
        
        self::$paginaBlacklist->clickButtonByExecuteScriptById(BT_BLOQUEAR_CHAMADOR_DESCONHECIDO_SIM);
        self::$paginaBlacklist->selectSelectorByIndex(self::$paginaBlacklist->getWebDriverSelectByName(SELECTOR_DESTINO), 1);
        self::$paginaBlacklist->clickButtonById(BT_ENVIAR);
    }

    public function testRestuararConfiguracoesBlacklist()
    {
        self::$paginaBlacklist->visit();
        self::$paginaBlacklist->clickButtonByLinkText(CONFIGURACOES);
        self::$paginaBlacklist->clickButtonByExecuteScriptById(BT_BLOQUEAR_CHAMADOR_DESCONHECIDO_NAO);
        $destinoSelector = self::$paginaBlacklist->getWebDriverSelectByName(SELECTOR_DESTINO);
        self::$paginaBlacklist->selectSelectorByValue($destinoSelector, "Finalizar_chamadas");
        $optionDestinoSelectorName = self::$paginaBlacklist->getValueSelectedItemFromSelector($destinoSelector) . "0";
        $optionDestinoSelector = self::$paginaBlacklist->getWebDriverSelectByName($optionDestinoSelectorName);
        self::$paginaBlacklist->selectSelectorByValue($optionDestinoSelector, "app-blackhole,busy,1");
        self::$paginaBlacklist->clickButtonById(BT_RESTAURAR);

        self::assertSame(true, self::$paginaBlacklist->getElementById(BT_BLOQUEAR_CHAMADOR_DESCONHECIDO_SIM)->isSelected(), "É esperado que o botão Sim (Bloquear Chamador Desconhecido) estejá selecionado");
        self::assertSame(false, self::$paginaBlacklist->getElementById(BT_BLOQUEAR_CHAMADOR_DESCONHECIDO_NAO)->isSelected(), "É esperado que o botão Não (Bloquear Chamador Desconhecido) estejá selecionado");
        self::assertSame("== Escolha uma opção ==", self::$paginaBlacklist->getSelectedItemFromSelector($destinoSelector), 'O Esperado é que o seletor Destino estivesse setado o valor "== Escolha uma opção =="');
    }

    public function testImportarBlacklist()
    {
        $arq = file("tests/Files/blacklist.csv");
        if($arq === false)
            throw new Exception("Could not read file");

        $number1 = substr($arq[1], 0, strpos($arq[1], ","));
        $number2 = substr($arq[2], 0, strpos($arq[2], ","));

        self::$paginaBlacklist->visit();
        $initialNumberOfBlacklistItems = self::$paginaBlacklist->countTableItems();
        self::$paginaBlacklist->clickButtonByLinkText(IMPORTAR);
        self::$paginaBlacklist->setFilePathById(BT_IMPORTAR_CSV_ID, "tests/Files/blacklist.csv");
        self::$paginaBlacklist->clickButtonById(BT_UPLOAD);
        self::$paginaBlacklist->clickButtonByLinkText(IMPORTAR);
        self::$paginaBlacklist->visit();

        self::assertSame($initialNumberOfBlacklistItems, self::$paginaBlacklist->countTableItems() - 2, "A importação não aconteceu corretamente");

        self::removeBlacklistByText($number2);
        self::removeBlacklistByText($number1);

        self::$paginaBlacklist->waitElementTextNotContainsByClass(TABLE_CLASS, $number1);
        self::assertSame($initialNumberOfBlacklistItems, self::$paginaBlacklist->countTableItems(), "O item não foi removido da Blacklist");
    }

    public function fillFields(array $data): void
    {
        self::$paginaBlacklist->clearAndSetFieldByName(FIELD_NUMERO_ID, $data['number']);
        self::$paginaBlacklist->clearAndSetFieldByName(FIELD_DESCRICAO_ID, $data['desc']);
    }

    public function addBlacklist(array $data): void
    {
        self::$paginaBlacklist->visit();
        self::$paginaBlacklist->clickButtonByLinkText(ADICIONAR);
        self::fillFields($data);
        self::$paginaBlacklist->clickButtonById(BT_SALVAR);
    }

    public function removeBlacklistByText(string $number): void
    {
        self::$paginaBlacklist->visit();
        self::$paginaBlacklist->waitElementTextContainsByClass(TABLE_CLASS, $number);
        self::$paginaBlacklist->clickButtonById(BT_REMOVER . $number);
    }

    public function entranceAdicionarEEditarBlacklist()
    {
        $number = "48987654321";
        $description = "Flávio";
        return [
            'entrance' => [$number, $description]
        ];
    }

    public static function tearDownAfterClass(): void
    {
        self::$driver->close();
    }
}
