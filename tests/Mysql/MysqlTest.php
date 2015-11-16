<?php

/**
 *
 * @author Dirk Merten
 */
class MysqlTest extends PHPUnit_Framework_TestCase {

	public function testParseDate() {
		$serverConnectionMock = $this->getMockBuilder('dmerten\Db\Mysql\ServerConnection')->disableOriginalConstructor()->getMock();
		$mysql = new \dmerten\Db\MySql\MySql($serverConnectionMock, 'mockdb');

		$date = $mysql->parseDate('2013-01-01 00:00:00');
		$this->assertEquals('01.01.2013 00:00:00', $date->format('d.m.Y H:i:s'));
	}

}
