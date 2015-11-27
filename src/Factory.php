<?php
namespace dmerten\Db;

use dmerten\Db\Memcache\Config as MemcacheConfig;
use dmerten\Db\Memcache\Memcache;
use dmerten\Db\MySql\MySql;
use dmerten\Db\MySql\ServerConnection;

/**
 * @author Dirk Merten
 */
class Factory
{
	/**
	 * @var array
	 */
	protected $mysqlServerConnections = [];
	/**
	 * @var Mysql[]
	 */
	protected $mysqlInstances = [];
	/**
	 * @var \dmerten\db\Config
	 */
	protected $config;
	/**
	 * @var Profiler
	 */
	protected $profiler;
	/**
	 * @var Memcache[]
	 */
	protected $memcacheInstances;
	/**
	 * @var Config[]
	 */
	protected $memcacheConfig;

	/**
	 * @param Config $configs
	 */
	public function __construct($configs)
	{
		$this->config = $configs;
	}

	/**
	 * @param Profiler $profiler
	 */
	public function setProfiler(Profiler $profiler)
	{
		$this->profiler = $profiler;
	}

	/**
	 * returns profiler
	 */
	public function getProfiler()
	{
		return $this->profiler;
	}

	/**
	 * @param $name
	 * @throws \InvalidArgumentException
	 */
	protected function configureMysql($name)
	{
		$config = $this->config->getMySqlConfigByName($name);
		if ($config === null) {
			throw new \InvalidArgumentException ("MySql DB name \"$name\" unknown");
		}

		$serverHash = $config->username . "@" . $config->dsn . "U";
		if (!array_key_exists($serverHash, $this->mysqlServerConnections)) {
			$this->mysqlServerConnections[$serverHash] = new ServerConnection($config->username, $config->password, $config->dsn);
		}
		$this->mysqlInstances[$name] = new MySql($this->mysqlServerConnections[$serverHash], $config->database);
		if ($this->profiler) {
			$this->mysqlInstances[$name]->setProfiler($this->profiler, $config->database);
		}
		if ($config->readonly) {
			$this->mysqlInstances[$name]->setReadonly($config->readonly);
		}
	}

	/**
	 * @param string $name
	 * @return MySql
	 * @throws \InvalidArgumentException
	 */
	public function getMysql($name)
	{
		if (!isset($this->mysqlInstances[$name])) {
			$this->configureMysql($name);
		}
		return $this->mysqlInstances[$name];
	}

	/**
	 * @param $name
	 * @return Memcache
	 * @throws \InvalidArgumentException
	 */
	public function getMemcache($name)
	{
		if (!isset($this->memcacheInstances[$name])) {
			$config = $this->config->getMemcacheConfigByName($name);
			if ($config === null) {
				throw new \InvalidArgumentException ("Memcache DB name \"$name\" unknown");
			}
			$this->memcacheInstances[$name] = new Memcache(new \Memcache(), $config->server, $config->compressed, $config->nameSpace);
			if ($this->profiler) {
				$profilerDbTypeStr = "mem_" . $name;
				$this->memcacheInstances[$name]->setProfiler($this->profiler, $profilerDbTypeStr);
			}
		}

		return $this->memcacheInstances[$name];
	}

}
