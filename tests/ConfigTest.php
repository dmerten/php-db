<?php

/**
 *
 * @author Dirk Merten
 */
class Vwd_DB_ConfigTest extends PHPUnit_Framework_TestCase {

	public function testgetMySqlConfigs() {
		$dbConfig = new \dmerten\Db\Config();
		$dbConfig->addMysql('testname', 'testdb', '10.10.128.102', 'testuser', 'testpassword', true);
		$mySqlConfigs = $dbConfig->getMySqlConfigs();
		$this->assertArrayHasKey('testname', $mySqlConfigs);
		$this->assertInstanceOf('dmerten\Db\Mysql\Config', $mySqlConfigs['testname']);
	}

	public function testgetMySqlConfigByName() {
		$dbConfig = new \dmerten\Db\Config();
		$dbConfig->addMysql('testname', 'testdb', '10.10.128.102', 'testuser', 'testpassword', true, true);
		$mySqlConfig = $dbConfig->getMySqlConfigByName('testname');
		$this->assertEquals('testdb', $mySqlConfig->database);
		$this->assertEquals('mysql:host=10.10.128.102;dbname=testdb', $mySqlConfig->dsn);
		$this->assertEquals('testuser', $mySqlConfig->username);
		$this->assertEquals('testpassword', $mySqlConfig->password);
		$this->assertTrue($mySqlConfig->readonly);
	}

	public function testIsMethods() {
		$dbConfig = new \dmerten\Db\Config();
		$dbConfig->addMysql('testname', 'testdb', '10.10.128.102', 'testuser', 'testpassword', true, true);
		$this->assertTrue($dbConfig->isMysqlUtf8('testname'));
		$this->assertTrue($dbConfig->isMysqlReadonly('testname'));
	}

	public function testGetMysqlServer() {
		$dbConfig = new \dmerten\Db\Config();
		$this->setExpectedException('InvalidArgumentException');
		$dbConfig->getMysqlServer('foo');
	}

	public function testGetMysqlDatabaseName() {
		$dbConfig = new \dmerten\Db\Config();
		$this->setExpectedException('InvalidArgumentException');
		$dbConfig->getMysqlDatabaseName('foo');
	}

	public function testGetMysqlUsername() {
		$dbConfig = new \dmerten\Db\Config();
		$this->setExpectedException('InvalidArgumentException');
		$dbConfig->getMysqlUsername('foo');
	}

	public function testIsMysqlUtf8() {
		$dbConfig = new \dmerten\Db\Config();
		$this->setExpectedException('InvalidArgumentException');
		$dbConfig->isMysqlUtf8('foo');
	}

	public function testIsMysqlReadonly() {
		$dbConfig = new \dmerten\Db\Config();
		$this->setExpectedException('InvalidArgumentException');
		$dbConfig->isMysqlReadonly('foo');
	}

	public function testGetMysqlPassword() {
		$dbConfig = new \dmerten\Db\Config();
		$this->setExpectedException('InvalidArgumentException');
		$dbConfig->getMysqlPassword('foo');
	}
}
