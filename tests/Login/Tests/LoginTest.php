<?php

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use uTech\Tests\Login\PageObject\LoginPageObject;
use uTech\Tests\Commons\Tests\PBXTestCase;
use uTech\Tests\Login\Functions\LoginFunctions;

include_once "config.inc";
include_once "tests/Commons/Constants/CommonsConstants.php";
include_once "tests/Login/Constants/LoginConstants.php";

class LoginTest extends PBXTestCase
{
    private static WebDriver $driver;
    private static LoginPageObject $paginaLogin;
    private static LoginFunctions $loginFunctions;

    public static function setUpBeforeClass(): void
    {
        global $driverHost, $browser, $visibleTest;
        self::$driver = RemoteWebDriver::create($driverHost, self::getCapabilities($browser, $visibleTest));
    }

    public function setUp(): void
    {
        self::$paginaLogin = new LoginPageObject(self::$driver, "");
        self::$loginFunctions = new LoginFunctions(self::$driver);
    }

    /**
     * @dataProvider entranceTestEfetuarLoginComErros
     */
    public function testEfetuarLoginComErros(string $user, string $password): void
    {
        global $ip;
        self::$loginFunctions->login($user, $password);
        self::assertSame(HTTP . $ip . BASE_URL, self::$driver->getCurrentURL());
        self::assertStringContainsString("Usuário ou Senha Inválidos", self::$driver->getPageSource(), "Frase de erro não encontrada");
        self::assertStringNotContainsString('Inicio', self::$paginaLogin->getSectionText("h1"), "Login foi realizado com dados incorretos");
    }

    public function testEfetuarLoginEmBranco(): void
    {
        global $ip, $pass;
        self::$loginFunctions->login("", $pass);
        self::assertSame(HTTP . $ip . BASE_URL, self::$driver->getCurrentURL());
        // self::assertStringContainsString("Campo obrigatório", self::$driver->getPageSource());
        self::assertStringNotContainsString('Inicio', self::$paginaLogin->getSectionText("h1"), "Login foi realizado incorretamente");
    }

    public function testEfetuarLogin(): void
    {
        global $ip, $user, $pass;
        self::$loginFunctions->login($user, $pass);
        self::assertSame('Inicio', self::$paginaLogin->getSectionText("h1"), "O login não foi efetuado ou o PABX está em outro idioma");

        self::$paginaLogin->clickButtonByClass(BT_MENU_LOGOFF);
        self::$paginaLogin->clickButtonById(BT_SAIR);
        self::$paginaLogin->waitPresenceOfElementLocatedByName(FIELD_LOGIN_USUARIO_NAME);
        self::assertSame(HTTP . $ip . BASE_URL, self::$driver->getCurrentURL(), "Não foi possível deslogar do PABX");
    }

    public function entranceTestEfetuarLoginComErros()
    {
        global $user, $pass;

        return [
            'usuarioInexistente'            => ["nimda159630", "nimda159630"],
            'loginComStringGrande'          => [LARGE_STRING, $pass],
            'loginComCaracteresEspeciais'   => [SPECIAL_CHARACTERS_STRING, $pass],
            'senhaComStringGrande'          => [$user, LARGE_STRING],
            'senhaComCaracteresEspeciais'   => [$user, SPECIAL_CHARACTERS_STRING],
            'senhaVazia'                    => [$user, ""]
        ];
    }

    public static function tearDownAfterClass(): void
    {
        self::$driver->close();
    }
}
