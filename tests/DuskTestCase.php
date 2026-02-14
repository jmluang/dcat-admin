<?php

namespace Tests;

use Dcat\Admin\Models\Administrator;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;
use RuntimeException;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication, BrowserExtension, InteractsWithDatabase;

    /**
     * PHPUnit 11+ is phasing out doc-comment metadata (e.g. @beforeClass).
     * Use the native lifecycle hook to ensure Chromedriver is started in CI.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::startChromeDriver();
    }

    public static function tearDownAfterClass(): void
    {
        static::stopChromeDriver();

        parent::tearDownAfterClass();
    }

    /**
     * @var Administrator
     */
    protected $user;

    /**
     * @var bool
     */
    protected $login = true;

    public function login(Browser $browser)
    {
        $browser->loginAs($this->getUser(), 'admin');
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->extendBrowser();

        $this->boot();
    }

    public function tearDown(): void
    {
        $this->destory();

        parent::tearDown();
    }

    /**
     * @param  \Facebook\WebDriver\Remote\RemoteWebDriver  $driver
     * @return \Laravel\Dusk\Browser
     */
    protected function newBrowser($driver)
    {
        $browser = (new Browser($driver))->resize(1566, 1080);

        $browser->resolver->prefix = 'html';

        if ($this->login) {
            $this->login($browser);
        }

        return $browser;
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            // "new" headless mode is the default direction for modern Chrome.
            '--headless=new',
            // Required for many CI environments (including GitHub Actions).
            '--no-sandbox',
            '--disable-dev-shm-usage',
            // Fixes "only local connections are allowed" style issues on newer Chromedriver/Chrome combos.
            '--remote-allow-origins=*',
            '--window-size=1920,1080',
        ]);

        $this->waitForChromeDriver();

        return RemoteWebDriver::create(
            'http://localhost:9515', DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY_W3C, $options
            )
        );
    }

    protected function waitForChromeDriver(int $timeoutSeconds = 10): void
    {
        $deadline = microtime(true) + $timeoutSeconds;

        while (microtime(true) < $deadline) {
            $fp = @fsockopen('127.0.0.1', 9515, $errno, $errstr, 0.2);
            if (is_resource($fp)) {
                fclose($fp);
                return;
            }

            usleep(100_000);
        }

        throw new RuntimeException('Chromedriver is not listening on 127.0.0.1:9515');
    }

    /**
     * Build the process to run the Chromedriver.
     *
     * @param  array  $arguments
     * @return \Symfony\Component\Process\Process
     *
     * @throws \RuntimeException
     */
    protected static function buildChromeProcess(array $arguments = [])
    {
        return (new ChromeProcess(static::$chromeDriver))->toProcess($arguments);
    }
}
