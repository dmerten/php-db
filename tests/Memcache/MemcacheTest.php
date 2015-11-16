<?php
use dmerten\Db\Memcache\Memcache;
use dmerten\Db\Memcache\MemcacheServer;
use dmerten\Db\Mysql\Profiler;

/** 
 *
 * @author Dirk Merten
 */

class Vwd_DB_Memcache_MemcacheTest extends PHPUnit_Framework_TestCase {

	public function testGet() {
		$peclMemcache = $this->getMockBuilder('Memcache')->disableOriginalConstructor()->getMock();

		$peclMemcache->expects($this->any())
			->method('get')
			->will($this->returnValue('bar'));


		$connections = new MemcacheServer();
		$connections->setHost('localhost');
		$connections->setPort(1337);
		$memcache = new Memcache($peclMemcache, array($connections), false, 'testing');


		$memcache->set('foo', 'bar');
		$this->assertSame('bar', $memcache->get('foo'));
	}

	public function testProfiler() {
		$peclMemcache = $this->getMockBuilder('Memcache')->disableOriginalConstructor()->getMock();

		$peclMemcache->expects($this->any())
			->method('get')
			->will($this->returnValue('bar'));


		$connections = new MemcacheServer();
		$connections->setHost('localhost');
		$connections->setPort(1337);
		$memcache = new Memcache($peclMemcache, array($connections), false, 'testing');
		$profiler = new Profiler();
		$memcache->setProfiler($profiler);

		$memcache->set('foo', 'bar');
		$memcache->get('foo');

		$this->assertCount(2, $profiler->getLogs());
	}

}
