# PBX E2E Tests

TODO: Describe project

# Pré-requisitos
- PHP 7.4
- Php-webdriver
    - Para a instalação da biblioteca é necessário o uso do Composer
        - Para instalação do Composer:<br />
            `curl -sS https://getcomposer.org/installer | php` <br />
        - Para instalação da biblioteca Php-webdriver:<br />
            `php composer.phar require php-webdriver/webdriver` <br />
- [Selenium standalone server](https://www.selenium.dev/downloads/)
    - Execução do Selenium:<br />
    `java -jar selenium-server-standalone-3.141.59.jar` <br />
- [Chromedriver](https://sites.google.com/a/chromium.org/chromedriver/downloads) para manipular o Google Chrome
    - Execução do Chromedriver:<br />
    `chromedriver --port=4444` <br />
- [Geckodriver](https://github.com/mozilla/geckodriver/releases) para manipular o Mozila Firefox.
     - Execução do Geckodriver:<br />
     `geckodriver` <br />
- Para a execução dos testes você pode utilizar separadamente, o Chromedriver ou Geckodriver, ou associar o uso do selenium com as opções anteriores.
- Para este projeto optou-se pelo uso do Selenium com o Chromedriver, após o download de ambos devem estar no mesmo diretório.

# Alterações de driver para manipulação dos navegadores

Os driver para manipular os arquivos são criados nas funções setUp() de todas as classes testes. Por opção do desenvolvedor foi escolhido o uso do Chrome como o navegador utilizados para teste.
A linha abaixo exemplifica a criação do driver em PHP: <br />
    - `self::$driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());` <br />
Caso opte por ocultar o navegador alterar a linha a cima para o seguinte trecho: <br />
    - `$options = new ChromeOptions(tests/);` <br />
    - `$options->addArguments( [ 'headless' ] );` <br />
    - `$capabilities = DesiredCapabilities::chrome(tests/);` <br />
    - `$capabilities->setCapability( ChromeOptions::CAPABILITY, $options );` <br />
    - `self::$driver = RemoteWebDriver::create($host, $capabilities);` <br />
Caso deseje utilizar o Mozilla Firefox utilizar a linha abaixo para criação do driver:
    - `self::$driver = RemoteWebDriver::create($host, DesiredCapabilities::firefox(tests/));` <br />
Caso opte por ocultar o navegador, seguir o exemplo do Chromedriver.
    
# Parâmetros pré execução
O arquivo [config.inc](config.inc) possui alguns parâmetros que devem ser ajustados antes da execução do teste de acordo com o PABX como o endereço IP, endereço IPV6, endereço de loopback, usuário, senha e uma variavel de timer onde 1000000 representa 1 segundo.

# Após o Download deste Repositório
Executar o composer para baixar as dependencias do projeto:<br />
 `composer update` <br />

# Execução dos Testes

## Execução de Todos os Testes
`php74 vendor/bin/phpunit`

## Execução de Todos os Testes
`php74 vendor/bin/phpunit {endereçoArquivo}`

## Executar Teste Especifico
`php74 vendor/bin/phpunit --filter {nomeDoTeste} {endereçoArquivo}`
