<?php

/**
 *
 * @author Dirk Merten
 */
class ProfilerTest extends PHPUnit_Framework_TestCase {

	public function testGetLogs() {
		$profiler = new \dmerten\Db\Mysql\Profiler();
		$query = 'SELECT 1 FROM dual';
		$database = 'mysql_database';
		$profiler->onBeforeCall($database, $query);
		$profiler->onAfterCall();

		$logs = $profiler->getLogs();
		$this->assertCount(1, $logs);
		$this->assertArrayHasKey('database', $logs[0]);
		$this->assertArrayHasKey('runtime', $logs[0]);
		$this->assertArrayHasKey('query', $logs[0]);
		$this->assertEquals($query, $logs[0]['query']);
		$this->assertEquals($database, $logs[0]['database']);
	}
}
