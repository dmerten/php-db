<?php
namespace dmerten\Db\Memcache;

use dmerten\Db\ProfilerInterface;

/**
 *
 * @author Dirk Merten
 */
class Memcache
{
	const DEFAULT_TTL = 300;
	/**
	 * @var ProfilerInterface
	 */
	private $profiler;
	/**
	 * @var MemcacheServer[]
	 */
	private $connections;
	/**
	 * @var
	 */
	private $nameSpace;
	/**
	 * @var bool
	 */
	private $compressed = false;
	/**
	 * @var Memcache
	 */
	private $memcache;

	/**
	 * @param \Memcache $memcache
	 * @param MemcacheServer[] $connections
	 * @param bool $compressed
	 * @param string $namespace
	 */
	public function __construct(\Memcache $memcache, array $connections, $compressed, $namespace)
	{
		$this->compressed = $compressed;
		$this->connections = $connections;
		$this->nameSpace = $namespace;
		$this->memcache = $memcache;

		foreach ($this->connections as $connection) {
			$this->memcache->addserver($connection->getHost(), $connection->getPort());
		}
	}

	/**
	 * @param $key
	 * @return array|string
	 */
	public function get($key)
	{
		$flags = $this->compressed ? MEMCACHE_COMPRESSED : 0;

		if ($this->profiler !== null) {
			$this->profiler->onBeforeCall('memcache get', $key);
		}

		$value = $this->memcache->get($this->getKey($key), $flags);

		if ($this->profiler !== null) {
			$this->profiler->onAfterCall();
		}

		return $value;
	}

	/**
	 * @param $key
	 * @return string
	 */
	private function getKey($key)
	{
		return $this->nameSpace . '.' . $key;
	}

	/**
	 * @param $key
	 * @param $value
	 * @param int $ttl
	 */
	public function set($key, $value, $ttl = 0)
	{
		$ttl = $ttl == 0 ? self::DEFAULT_TTL : $ttl;
		$flags = $this->compressed ? MEMCACHE_COMPRESSED : 0;


		if ($this->profiler !== null) {
			$this->profiler->onBeforeCall('memcache set', $key);
		}

		$this->memcache->set($this->getKey($key), $value, $flags, $ttl);

		if ($this->profiler !== null) {
			$this->profiler->onAfterCall();
		}
	}

	/**
	 * close connection
	 */
	public function __destruct()
	{
		$this->memcache->close();
	}

	/**
	 * @param ProfilerInterface $profiler
	 */
	public function setProfiler(ProfilerInterface $profiler)
	{
		$this->profiler = $profiler;
	}


}
