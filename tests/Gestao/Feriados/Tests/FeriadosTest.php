<?php

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use uTech\Tests\Gestao\Feriados\PageObject\FeriadosAddPageObject;
use uTech\Tests\Gestao\Feriados\PageObject\FeriadosPageObject;
use uTech\Tests\Commons\Tests\PBXTestCase;
use uTech\Tests\Login\Functions\LoginFunctions;

include_once 'config.inc';
include_once "tests/Commons/Constants/CommonsConstants.php";
include_once "tests/Gestao/Feriados/Constants/FeriadosConstants.php";

class FeriadosTest extends PBXTestCase
{
    private static WebDriver $driver;
    private static FeriadosPageObject $paginaFeriados;
    private static FeriadosAddPageObject $paginaFeriado;

    public static function setUpBeforeClass(): void
    {
        global $user, $pass, $driverHost, $browser, $visibleTest;
        self::$driver = RemoteWebDriver::create($driverHost, self::getCapabilities($browser, $visibleTest));

        $loginFunctions = new LoginFunctions(self::$driver);
        $loginFunctions->login($user, $pass);
    }

    public function setUp(): void
    {
        self::$paginaFeriados = new FeriadosPageObject(self::$driver, URL_FERIADOS_PAGE);
        self::$paginaFeriado = new FeriadosAddPageObject(self::$driver, URL_FERIADOS_ADD_PAGE);
    }

    public function testAdicionarFeriadoSemDados(): void
    {
        self::$paginaFeriados->visit();
        $initialNumberOfHolidaysItems = self::$paginaFeriados->countTableItems();
        self::addFeriado(['name' => "", 'desc' => "", 'date' => "", 'start' => "", 'finish' => ""]);
        self::$paginaFeriado->acceptAlert();
        self::$paginaFeriado->clickButtonByLinkText(VOLTAR);

        self::assertSame($initialNumberOfHolidaysItems, self::$paginaFeriados->countTableItems(), "O feriado não deveria ter sido adicionado");
    }

    /**
     * @dataProvider entranceAdicionarEEditarFeriados
     */
    public function testAdicionarFeriado($name, $description, $date, $startTime, $finishTime): void
    {
        self::$paginaFeriados->visit();
        $initialNumberOfHolidaysItems = self::$paginaFeriados->countTableItems();

        self::addFeriado(['name' => $name, 'desc' => $description, 'date' => $date, 'start' => $startTime, 'finish' => $finishTime]);
        self::assertSame($initialNumberOfHolidaysItems, self::$paginaFeriados->countTableItems() - 1, "O feriado não foi adicionado corretamente");

        self::$paginaFeriados->clickButtonByLinkText(EDITAR, self::$paginaFeriados->getFeriadoTableIndex($name));
        self::assertSame($name, self::$paginaFeriado->getFieldValueByName(FIELD_NOME), "O valor do campo 'Nome' não é o esperado, o esperado seria $name");
        self::assertSame($description, self::$paginaFeriado->getFieldValueByName(FIELD_DESCRIPTION), "O valor do campo 'Descrição' não é o esperado, o esperado seria $description");
        self::assertSame($date, self::$paginaFeriado->getFieldValueByName(FIELD_DATA), "O valor do campo 'Data' não é o esperado, o esperado seria $date");
        self::assertSame($startTime, self::$paginaFeriado->getFieldValueByName(FIELD_INICIO), "O valor do campo 'Início' não é o esperado, o esperado seria $startTime");
        self::assertSame($finishTime, self::$paginaFeriado->getFieldValueByName(FIELD_FIM), "O valor do campo 'Fim' não é o esperado, o esperado seria $finishTime");
        self::$paginaFeriado->clickButtonByLinkText(VOLTAR);

        self::removeFeriado($name);

        self::assertSame($initialNumberOfHolidaysItems, self::$paginaFeriados->countTableItems(), "O feriado não foi removido corretamente");
        self::$paginaFeriados->clickButtonById(BT_APLICAR);
    }

    /**
     * @dataProvider entranceAdicionarEEditarFeriados
     */
    public function testEditarFeriado($name, $description, $date, $startTime, $finishTime): void
    {
        self::$paginaFeriados->visit();
        $initialNumberOfHolidaysItems = self::$paginaFeriados->countTableItems();

        self::addFeriado(['name' => "Dia de São José", 'desc' => "Dia da cidade", 'date' => "19/03", 'start' => "00:00", 'finish' => "05:00"]);
        self::$paginaFeriados->visit();
        self::assertSame($initialNumberOfHolidaysItems, self::$paginaFeriados->countTableItems() - 1, "O feriado não foi adicionado corretamente");

        self::$paginaFeriados->clickButtonByLinkText(EDITAR, self::$paginaFeriados->getFeriadoTableIndex("Dia de São José"));
        self::fillFields(['name' => $name, 'desc' => $description, 'date' => $date, 'start' => $startTime, 'finish' => $finishTime]);
        self::$paginaFeriado->clickButtonWithText(SALVAR);

        self::$paginaFeriados->clickButtonByLinkText(EDITAR, self::$paginaFeriados->getFeriadoTableIndex($name));
        self::assertSame($name, self::$paginaFeriado->getFieldValueByName(FIELD_NOME), "O valor do campo 'Nome' não é o esperado, o esperado seria $name");
        self::assertSame($description, self::$paginaFeriado->getFieldValueByName(FIELD_DESCRIPTION), "O valor do campo 'Descrição' não é o esperado, o esperado seria $description");
        self::assertSame($date, self::$paginaFeriado->getFieldValueByName(FIELD_DATA), "O valor do campo 'Data' não é o esperado, o esperado seria $date");
        self::assertSame($startTime, self::$paginaFeriado->getFieldValueByName(FIELD_INICIO), "O valor do campo 'Início' não é o esperado, o esperado seria $startTime");
        self::assertSame($finishTime, self::$paginaFeriado->getFieldValueByName(FIELD_FIM), "O valor do campo 'Fim' não é o esperado, o esperado seria $finishTime");
        self::$paginaFeriado->clickButtonByLinkText(VOLTAR);

        self::removeFeriado($name);

        self::assertSame($initialNumberOfHolidaysItems, self::$paginaFeriados->countTableItems(), "O feriado não foi removido corretamente");
        self::$paginaFeriados->clickButtonById(BT_APLICAR);
    }

    /**
     * @dataProvider entranceTestPesquisarFeriado
     */
    public function testPesquisarFeriado(string $name, string $date): void
    {
        self::$paginaFeriados->visit();

        if (!self::isDateAvailable($date)) {
            $this->expectExceptionMessage("Teste não executado, a data informada já foi utilizada ");
        }

        $initialNumberOfHolidaysItems = self::$paginaFeriados->countTableItems();
        $initialFilterResultCount = self::getFilterResultCount($name);

        self::addFeriado(['name' => $name, 'desc' => "", 'date' => $date, 'start' => "", 'finish' => ""]);

        $expectedNumberOfHolidays = $initialNumberOfHolidaysItems + 1;
        $expectedFilterResultCount = $initialFilterResultCount + 1;

        $filterResultCount = self::getFilterResultCount($name);
        $currentNumberOfHolidays = self::$paginaFeriados->countTableItems();

        self::assertSame($expectedFilterResultCount, $filterResultCount, "Foi encontrado um número de feriados diferente do esperado, era esperado $expectedFilterResultCount e foram encontrados $filterResultCount");
        self::assertSame($expectedNumberOfHolidays, $currentNumberOfHolidays, "O filtro não limpou corretamente os resultados");

        self::removeFeriado($name);
    }

    public function fillFields(array $data): void
    {
        self::$paginaFeriado->clearAndSetFieldByName(FIELD_NOME, $data['name']);
        self::$paginaFeriado->clearAndSetFieldByName(FIELD_DESCRIPTION, $data['desc']);
        self::$paginaFeriado->clearAndSetFieldByName(FIELD_DATA, $data['date']);
        self::$paginaFeriado->clearAndSetFieldByName(FIELD_INICIO, $data['start']);
        self::$paginaFeriado->clearAndSetFieldByName(FIELD_FIM, $data['finish']);
    }

    public function addFeriado(array $data): void
    {
        self::$paginaFeriado->visit();
        self::fillFields($data);
        self::$paginaFeriado->clickButtonWithText(SALVAR);
    }

    public function removeFeriado(string $name): void
    {
        self::$paginaFeriados->visit();
        self::$paginaFeriados->clickButtonByLinkText(REMOVER, self::$paginaFeriados->getFeriadoTableIndex($name));
        self::$paginaFeriado->clickButtonByClass(BT_REMOVER_FERIADO_CLASS);
    }

    public function isDateAvailable(string $date): bool
    {
        return self::$paginaFeriados->getFeriadoTableIndex($date) === -1;
    }

    public function applyFilter(string $filterText): void
    {
        self::$paginaFeriados->clearAndSetFieldByName(FIELD_PESQUISA, $filterText);
        self::$paginaFeriados->clickButtonByType(BT_PESQUISAR_TYPE);
    }

    public function getFilterResultCount(string $filterText): int
    {
        self::applyFilter($filterText);
        $result = self::$paginaFeriados->countTableItems();
        self::$paginaFeriados->clickButtonByClass(BT_FECHAR_PESQUISA_CLASS);

        return $result;
    }

    public function entranceAdicionarEEditarFeriados()
    {
        $name = "John Smith";
        $description = "Aniversário";
        $date = "18/04";
        $startTime = "00:00";
        $finishTime = "23:59";
        $trash = "";
        return [
            'entrance1' => [$name, $description, $date, $startTime, $finishTime],
            'entrance2' => [$name, $description, $date, $trash, $trash]
        ];
    }

    public function entranceTestPesquisarFeriado()
    {
        $name = "Dia do estagiário";
        $date = "18/08";
        $name2 = "Dia do engenheiro";
        $date2 = "11/12";
        return [
            'entrance1' => [$name, $date],
            'entrance2' => [$name2, $date2]
        ];
    }

    public static function tearDownAfterClass(): void
    {
        self::$driver->close();
    }
}
