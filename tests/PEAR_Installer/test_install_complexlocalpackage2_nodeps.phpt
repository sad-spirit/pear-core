--TEST--
PEAR_Installer->install() with complex local package.xml 2.0 [preferred_state = alpha, nodeps]
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$ch = new PEAR_ChannelFile;
$ch->setName('smork');
$ch->setSummary('smork');
$ch->setDefaultPEARProtocols();
$reg = &$config->getRegistry();
$phpunit->assertTrue($reg->addChannel($ch), 'smork setup');
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'package2.xml';
$pathtobarxml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'Bar-1.5.2.tgz';
$pathtofoobarxml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'Foobar-1.5.0a1.tgz';
$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/Bar-1.5.2.tgz', $pathtobarxml);
$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/Foobar-1.5.0a1.tgz', $pathtofoobarxml);
$GLOBALS['pearweb']->addXmlrpcConfig('pear.php.net', 'package.getDepDownloadURL',
    array('2.0',
         array('name' => 'Bar', 'channel' => 'pear.php.net', 'min' => '1.0.0'),
         array('channel' => 'pear.php.net', 'package' => 'PEAR1', 'version' => '1.5.0a1'), 'alpha'),
    array('version' => '1.5.2',
          'info' =>
          array(
            'package' => 'Bar',
            'channel' => 'pear.php.net',
            'license' => 'PHP License',
            'summary' => 'test',
            'description' => 'test',
            'releasedate' => '2003-12-06 00:26:42',
            'state' => 'stable',
            'deps' =>
            array(
                'required' =>
                array(
                    'php' =>
                    array(
                        'min' => '4.3.6',
                        'max' => '6.0.0',
                    ),
                    'pearinstaller' =>
                    array(
                        'min' => '1.4.0a1',
                    ),
                    'package' =>
                    array(
                        'name' => 'Foobar',
                        'channel' => 'smork',
                    ),
                ),
            ),
          ),
          'url' => 'http://www.example.com/Bar-1.5.2'));
$GLOBALS['pearweb']->addXmlrpcConfig('smork', 'package.getDepDownloadURL',
    array('2.0',
         array('name' => 'Foobar', 'channel' => 'smork'),
         array('channel' => 'pear.php.net', 'package' => 'Bar', 'version' => '1.5.2'), 'alpha'),
    array('version' => '1.5.0a1',
          'info' =>
          array(
            'package' => 'Foobar',
            'channel' => 'smork',
            'license' => 'PHP License',
            'summary' => 'test',
            'description' => 'test',
            'releasedate' => '2003-12-06 00:26:42',
            'state' => 'alpha',
          ),
          'url' => 'http://www.example.com/Foobar-1.5.0a1'));
$_test_dep->setPHPVersion('4.3.11');
$_test_dep->setPEARVersion('1.4.0a1');
$dp = &new test_PEAR_Downloader($fakelog, array('nodeps' => true), $config);
$phpunit->assertNoErrors('after create');
$config->set('preferred_state', 'alpha');
$result = &$dp->download(array($pathtopackagexml));
$phpunit->assertEquals(1, count($result), 'return');
$phpunit->assertIsa('test_PEAR_Downloader_Package', $result[0], 'right class 0');
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf = $result[0]->getPackageFile(), 'right kind of pf 0');
$phpunit->assertEquals('PEAR1', $pf->getPackage(), 'right package');
$phpunit->assertEquals('pear.php.net', $pf->getChannel(), 'right channel');
$dlpackages = $dp->getDownloadedPackages();
$phpunit->assertEquals(1, count($dlpackages), 'downloaded packages count');
$phpunit->assertEquals(3, count($dlpackages[0]), 'internals package count');
$phpunit->assertEquals(array('file', 'info', 'pkg'), array_keys($dlpackages[0]), 'indexes');
$phpunit->assertEquals($pathtopackagexml,
    $dlpackages[0]['file'], 'file');
$phpunit->assertIsa('PEAR_PackageFile_v2',
    $dlpackages[0]['info'], 'info');
$phpunit->assertEquals('PEAR1',
    $dlpackages[0]['pkg'], 'PEAR1');
$after = $dp->getDownloadedPackages();
$phpunit->assertEquals(0, count($after), 'after getdp count');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 3,
    1 => '+ tmp dir created at ' . $dp->getDownloadDir(),
  ),
  1 =>
  array (
    0 => 0,
    1 => 'warning: pear/PEAR1 requires package "pear/Bar" (version >= 1.0.0)',
  ),
), $fakelog->getLog(), 'log messages');
$phpunit->assertEquals(array (
), $fakelog->getDownload(), 'download callback messages');
$installer->sortPackagesForInstall($result);
$installer->setDownloadedPackages($result);
$phpunit->assertNoErrors('set of downloaded packages');
$installer->setOptions($dp->getOptions());

$ret = &$installer->install($result[0], $dp->getOptions());
$phpunit->assertNoErrors('after install');
$phpunit->assertEquals(array (
  'attribs' => 
  array (
    'version' => '2.0',
    'xmlns' => 'http://pear.php.net/dtd/package-2.0',
    'xmlns:tasks' => 'http://pear.php.net/dtd/tasks-1.0',
    'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
    'xsi:schemaLocation' => 'http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd',
  ),
  'name' => 'PEAR1',
  'channel' => 'pear.php.net',
  'summary' => 'PEAR Base System',
  'description' => 'The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the alpha-quality PEAR_Exception PHP5 error handling mechanism
 * the beta-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling of common operations
   with files and directories
 * the PEAR base class',
  'lead' => 
  array (
    0 => 
    array (
      'name' => 'Stig Bakken',
      'user' => 'ssb',
      'email' => 'stig@php.net',
      'active' => 'yes',
    ),
    1 => 
    array (
      'name' => 'Tomas V.V.Cox',
      'user' => 'cox',
      'email' => 'cox@idecnet.com',
      'active' => 'yes',
    ),
    2 => 
    array (
      'name' => 'Pierre-Alain Joye',
      'user' => 'pajoye',
      'email' => 'pajoye@pearfr.org',
      'active' => 'yes',
    ),
    3 => 
    array (
      'name' => 'Greg Beaver',
      'user' => 'cellog',
      'email' => 'cellog@php.net',
      'active' => 'yes',
    ),
  ),
  'developer' => 
  array (
    'name' => 'Martin Jansen',
    'user' => 'mj',
    'email' => 'mj@php.net',
    'active' => 'yes',
  ),
  'date' => '2004-09-30',
  'version' => 
  array (
    'release' => '1.5.0a1',
    'api' => '1.4.0',
  ),
  'stability' => 
  array (
    'release' => 'alpha',
    'api' => 'alpha',
  ),
  'license' => 
  array (
    'attribs' => 
    array (
      'uri' => 'http://www.php.net/license/3_0.txt',
    ),
    '_content' => 'PHP License',
  ),
  'notes' => 'stuff',
  'contents' => 
  array (
    'dir' => 
    array (
      'attribs' => 
      array (
        'name' => '/',
      ),
      'file' => 
      array (
        'attribs' => 
        array (
          'name' => 'foo.php',
          'role' => 'php',
        ),
      ),
    ),
  ),
  'dependencies' => 
  array (
    'required' => 
    array (
      'php' => 
      array (
        'min' => '4.2.0',
        'max' => '6.0.0',
      ),
      'pearinstaller' => 
      array (
        'min' => '1.4.0dev13',
      ),
      'package' => 
      array (
        0 => 
        array (
          'name' => 'Foo',
          'channel' => 'pear.php.net',
          'conflicts' => '',
        ),
        1 => 
        array (
          'name' => 'Bar',
          'channel' => 'pear.php.net',
          'min' => '1.0.0',
        ),
      ),
    ),
  ),
  'phprelease' => '',
  'filelist' => 
  array (
    'foo.php' => 
    array (
      'name' => 'foo.php',
      'role' => 'php',
      'installed_as' => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'foo.php',
    ),
  ),
  'dirtree' => 
  array (
    $temp_path . DIRECTORY_SEPARATOR . 'php' => true,
  ),
  'old' => 
  array (
    'version' => '1.5.0a1',
    'release_date' => '2004-09-30',
    'release_state' => 'alpha',
    'release_license' => 'PHP License',
    'release_notes' => 'stuff',
    'release_deps' => 
    array (
      0 => 
      array (
        'type' => 'php',
        'rel' => 'le',
        'version' => '6.0.0',
        'optional' => 'no',
      ),
      1 => 
      array (
        'type' => 'php',
        'rel' => 'ge',
        'version' => '4.2.0',
        'optional' => 'no',
      ),
      2 => 
      array (
        'type' => 'pkg',
        'channel' => 'pear.php.net',
        'name' => 'PEAR',
        'rel' => 'ge',
        'version' => '1.4.0dev13',
        'optional' => 'no',
      ),
      3 => 
      array (
        'type' => 'pkg',
        'channel' => 'pear.php.net',
        'name' => 'Foo',
        'rel' => 'not',
      ),
      4 => 
      array (
        'type' => 'pkg',
        'channel' => 'pear.php.net',
        'name' => 'Bar',
        'rel' => 'ge',
        'version' => '1.0.0',
        'optional' => 'no',
      ),
    ),
    'maintainers' => 
    array (
      0 => 
      array (
        'name' => 'Stig Bakken',
        'email' => 'stig@php.net',
        'active' => 'yes',
        'handle' => 'ssb',
        'role' => 'lead',
      ),
      1 => 
      array (
        'name' => 'Tomas V.V.Cox',
        'email' => 'cox@idecnet.com',
        'active' => 'yes',
        'handle' => 'cox',
        'role' => 'lead',
      ),
      2 => 
      array (
        'name' => 'Pierre-Alain Joye',
        'email' => 'pajoye@pearfr.org',
        'active' => 'yes',
        'handle' => 'pajoye',
        'role' => 'lead',
      ),
      3 => 
      array (
        'name' => 'Greg Beaver',
        'email' => 'cellog@php.net',
        'active' => 'yes',
        'handle' => 'cellog',
        'role' => 'lead',
      ),
      4 => 
      array (
        'name' => 'Martin Jansen',
        'email' => 'mj@php.net',
        'active' => 'yes',
        'handle' => 'mj',
        'role' => 'developer',
      ),
    ),
  ),
  'xsdversion' => '2.0',
)
, $ret, 'return of install 2');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'foo.php',
    'installed file');
$reg = &$config->getRegistry();
$info = $reg->packageInfo('PEAR1');
$phpunit->assertTrue(isset($info['_lastmodified']), 'lastmodified is set?');
unset($info['_lastmodified']);
$phpunit->assertEquals($ret, $info, 'test installation, PEAR1');
echo 'tests done';
?>
--EXPECT--
tests done