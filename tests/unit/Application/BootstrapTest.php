<?php
/**
 * phpDocumentor
 *
 * PHP Version 5.4
 *
 * @copyright 2010-2014 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
namespace phpDocumentor;

use org\bovigo\vfs\vfsStream;
use phpDocumentor\Application\Bootstrap;
use PHPUnit_Framework_TestCase;

/**
 * Test class for \phpDocumentor\Application\Bootstrap.
 *
 * @covers phpDocumentor\Application\Bootstrap
 */
class BootstrapTest extends PHPUnit_Framework_TestCase
{
    /**
     * Directory structure when phpdocumentor is installed using composer.
     *
     * @var array
     */
    protected $composerInstalledStructure = array(
        'dummy' => array(
            'vendor' => array(
                'phpDocumentor' => array(
                    'phpDocumentor' => array(
                        'src' => array(
                            'phpDocumentor' => array(),
                        ),
                    ),
                ),
            ),
        ),
    );

    /**
     * Directory structure when phpdocumentor is installed from git.
     *
     * @var array
     */
    protected $standaloneStructure = array(
        'dummy' => array(
            'vendor' => array(),
            'src' => array(
                'phpDocumentor' => array(),
            ),
            'test' => array(),
        ),
    );

    /**
     * @covers phpDocumentor\Application\Bootstrap::createInstance
     */
    public function testCreatingAnInstanceUsingStaticFactoryMethod()
    {
        $this->assertInstanceOf('phpDocumentor\Application\Bootstrap', Bootstrap::createInstance());
    }

    /**
     * @covers phpDocumentor\Application\Bootstrap::initialize
     */
    public function testInitializingTheApplication()
    {
        $bootstrap = Bootstrap::createInstance();
        $this->assertInstanceOf('phpDocumentor\Application\Application', $bootstrap->initialize());
    }

    /**
     * @covers phpDocumentor\Application\Bootstrap::findVendorPath
     */
    public function testFindVendorPathStandAloneInstall()
    {
        vfsStream::setup('root', null, $this->standaloneStructure);
        $bootstrap = Bootstrap::createInstance();

        $baseDir = vfsStream::url('root/dummy/src/Application');
        $this->assertSame('vfs://root/dummy/src/Application/../../vendor', $bootstrap->findVendorPath($baseDir));
    }

    /**
     * @covers phpDocumentor\Application\Bootstrap::findVendorPath
     */
    public function testFindVendorPathComposerInstalled()
    {
        $root = vfsStream::setup('root', null, $this->composerInstalledStructure);
        vfsStream::newFile('composer.json')->at($root->getChild('dummy'));

        $bootstrap = Bootstrap::createInstance();
        $baseDir = vfsStream::url('root/dummy/vendor/phpDocumentor/phpDocumentor/src/Application');
        $this->assertSame(
            'vfs://root/dummy/vendor/phpDocumentor/phpDocumentor/src/Application/../../../../../vendor',
            $bootstrap->findVendorPath($baseDir)
        );
    }

    /**
     * Tests if exception is thrown when no autoloader is present
     *
     * @expectedException \RuntimeException
     * @covers phpDocumentor\Application\Bootstrap::createAutoloader
     */
    public function testCreateAutoloaderNoAutoloader()
    {
        vfsStream::setup('root', null, $this->standaloneStructure);
        $bootstrap = Bootstrap::createInstance();
        $bootstrap->createAutoloader(vfsStream::url('root/dummy/vendor'));
    }

    /**
     * checks autoload.php is required and returned by createAutoloader
     *
     * @covers phpDocumentor\Application\Bootstrap::createAutoloader
     */
    public function testCreateAutoloader()
    {
        $root = vfsStream::setup('root', null, $this->standaloneStructure);
        vfsStream::newFile('autoload.php')->withContent('<?php return true;')
            ->at($root->getChild('dummy')->getChild('vendor'));

        $bootstrap = Bootstrap::createInstance();
        $this->assertTrue($bootstrap->createAutoloader(vfsStream::url('root/dummy/vendor')));
    }
}