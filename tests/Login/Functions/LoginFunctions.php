<?php

namespace uTech\Tests\Login\Functions;

use Facebook\WebDriver\WebDriver;
use uTech\Tests\Login\PageObject\LoginPageObject;

include_once "tests/Login/Constants/LoginConstants.php";

class LoginFunctions
{
    private LoginPageObject $paginaLogin;

    public function __construct(WebDriver $driver)
    {
        $this->paginaLogin = new LoginPageObject($driver, "");
    }

    public function login(string $user, string $password): void
    {
        $this->paginaLogin->visit();
        $this->fillFields(['user' => $user, 'pass' => $password]);
        $this->paginaLogin->clickButtonByName(BT_ACESSO_NAME);
    }

    public function fillFields(array $data): void
    {
        $this->paginaLogin->clearAndSetFieldByName(FIELD_LOGIN_USUARIO_NAME, $data['user']);
        $this->paginaLogin->clearAndSetFieldByName(FIELD_LOGIN_SENHA_NAME, $data['pass']);
    }
}
