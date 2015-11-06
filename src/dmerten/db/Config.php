<?php
namespace dmerten\db;

use dmerten\db\Memcache\Config as MemCacheConfig;
use dmerten\db\Memcache\MemcacheServer;
use dmerten\db\MySql\Config as MySqlConfig;

/**
 * Class Config
 *
 * @package dmerten\db
 */
class Config
{
	/**
	 * @var array
	 */
	protected $mysqlServers = array();
	/**
	 * @var array
	 */
	protected $mysqlDatabases = array();
	/**
	 * @var array
	 */
	protected $mysqlUsernames = array();
	/**
	 * @var array
	 */
	protected $mysqlPasswords = array();
	/**
	 * @var array
	 */
	protected $mysqlUtf8 = array();
	/**
	 * @var array
	 */
	protected $mysqlReadonly = array();
	/**
	 * @var array
	 */
	protected $memcacheServer = array();
	/**
	 * @var array
	 */
	protected $memcacheConfig = array();
	/**
	 * @var array
	 */
	private $names = array();

	/**
	 * Add a mew Mysql Database
	 *
	 * @param string $name
	 * @param string $db
	 * @param string $server
	 * @param string $username
	 * @param string $password
	 * @param bool $utf8
	 * @param bool $readonly
	 */
	public function addMysql($name, $db, $server, $username, $password, $utf8 = false, $readonly = false)
	{
		$this->mysqlDatabases[$name] = $db;
		$this->mysqlServers[$name] = $server;
		$this->mysqlUsernames[$name] = $username;
		$this->mysqlPasswords[$name] = $password;
		$this->mysqlUtf8[$name] = $utf8;
		$this->mysqlReadonly[$name] = $readonly;
		$this->names[] = $name;
	}

	/**
	 * Is a UTF8 Connection
	 *
	 * @param string $name
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function isMysqlUtf8($name)
	{
		if (isset($this->mysqlUtf8[$name])) {
			return $this->mysqlUtf8[$name];
		}
		throw new \InvalidArgumentException("Db name \"$name\" unknown");
	}

	/**
	 * @return MySqlConfig[]
	 */
	public function getMySqlConfigs()
	{
		$return = array();

		foreach ($this->names as $name) {
			$return[$name] = $this->getMySqlConfigByName($name);
		}

		return $return;
	}

	/**
	 * @param $name
	 * @return \dmerten\db\MySql\Config
	 */
	public function getMySqlConfigByName($name)
	{
		$mysqlConfig = new MySqlConfig();
		$mysqlConfig->database = $this->getMysqlDatabaseName($name);
		$mysqlConfig->dsn = 'mysql:host=' . $this->getMysqlServer($name) . ';dbname=' . $this->getMysqlDatabaseName($name);
		$mysqlConfig->password = $this->getMysqlPassword($name);
		$mysqlConfig->username = $this->getMysqlUsername($name);
		$mysqlConfig->readonly = $this->isMysqlReadonly($name);

		return $mysqlConfig;
	}

	/**
	 * Returns the Db name for $name
	 *
	 * @param string $name
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getMysqlDatabaseName($name)
	{
		if (isset($this->mysqlDatabases[$name])) {
			return $this->mysqlDatabases[$name];
		}
		throw new \InvalidArgumentException("DB name \"$name\" unknown");
	}

	/**
	 * Returns the server name for $name
	 *
	 * @param string $name
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getMysqlServer($name)
	{
		if (isset($this->mysqlServers[$name])) {
			return $this->mysqlServers[$name];
		}
		throw new \InvalidArgumentException("DB name \"$name\" unknown");
	}

	/**
	 * Returns the Mysql Password for $name
	 *
	 * @param string $name
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getMysqlPassword($name)
	{
		if (isset($this->mysqlPasswords[$name])) {
			return $this->mysqlPasswords[$name];
		}
		throw new \InvalidArgumentException("DB name \"$name\" unknown");
	}

	/**
	 * Returns the Mysql Username for $name
	 *
	 * @param string $name
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getMysqlUsername($name)
	{
		if (isset($this->mysqlUsernames[$name])) {
			return $this->mysqlUsernames[$name];
		}
		throw new \InvalidArgumentException("DB name \"$name\" unknown");
	}

	/**
	 * @param string $name
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function isMysqlReadonly($name)
	{
		if (isset($this->mysqlReadonly[$name])) {
			return $this->mysqlReadonly[$name];
		}
		throw new \InvalidArgumentException("DB name \"$name\" unknown");
	}

	/**
	 * @param $name
	 * @param $host
	 * @param $port
	 */
	public function addMemcacheServer($name, $host, $port)
	{
		if (!isset($this->memcacheConfig[$name])) {
			$this->memcacheConfig[$name] = new MemCacheConfig();
		}

		$server = new MemcacheServer();
		$server->setHost($host);
		$server->setPort($port);
		$this->memcacheConfig[$name]->server[] = $server;
		$this->names[] = $name;
	}

	/**
	 * @param $name
	 * @return MemCacheConfig
	 */
	public function getMemcacheConfigByName($name)
	{
		return isset($this->memcacheConfig[$name]) ? $this->memcacheConfig[$name] : null;
	}

	/**
	 * @param string $name
	 * @param bool $compressed
	 * @param string $nameSpace
	 */
	public function addMemcacheConfig($name, $compressed, $nameSpace)
	{
		if (!isset($this->memcacheConfig[$name])) {
			$this->memcacheConfig[$name] = new MemCacheConfig();
		}

		$this->memcacheConfig[$name]->compressed = $compressed;
		$this->memcacheConfig[$name]->nameSpace = $nameSpace;
	}

}

