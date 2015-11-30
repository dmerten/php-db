<?php
namespace dmerten\Db\MySql;

use dmerten\Db\ProfilerInterface;

/**
 * Class MySql
 *
 * @package dmerten\db\MySql
 */
class MySql
{
	/**
	 * @var ProfilerInterface
	 */
	protected $profiler;
	/**
	 * @var string
	 */
	protected $profilerDbTypeStr;
	/**
	 *
	 * @var ServerConnection
	 */
	protected $connection = null;
	/**
	 *
	 * @var bool
	 */
	protected $readonly = false;
	/**
	 *
	 * @var string
	 */
	protected $dbName;

	/**
	 * @param ServerConnection $connection
	 * @param string $dbName
	 */
	public function __construct(ServerConnection $connection, $dbName)
	{
		$this->dbName = $dbName;
		$this->connection = $connection;
	}

	/**
	 * @param ProfilerInterface $profiler
	 * @param string
	 */
	public function setProfiler(ProfilerInterface $profiler, $profilerDbTypeStr)
	{
		$this->profiler = $profiler;
		$this->profilerDbTypeStr = $profilerDbTypeStr;
	}

	/**
	 * @param bool $readonly
	 */
	public function setReadonly($readonly)
	{
		$this->readonly = (bool)$readonly;
	}


	/**
	 * @param string $string
	 * @return string
	 */
	public function realEscape($string)
	{
		$result = $this->getPdo()->quote($string);
		return mb_substr($result, 1, -1);
	}

	/**
	 * @return \PDO
	 */
	protected function getPdo()
	{
		return $this->connection->getPdoForDb($this->dbName);
	}

	/**
	 * Returns a Query Object that can be bound to or executed
	 *
	 * @param string $sql
	 * @return Query
	 */
	public function query($sql)
	{
		$query = new Query($this->getPdo(), $sql);
		if ($this->profiler) {
			$query->setProfiler($this->profiler, $this->profilerDbTypeStr);
		}
		if ($this->readonly) {
			$query->setReadonly($this->readonly);
		}
		return $query;
	}

	/**
	 * @return bool
	 */
	public function isConnected()
	{
		return $this->connection->isConnected();
	}

	/**
	 * Parse a DateString that was returned as column value from a MySQL Query
	 *
	 * @param string $dateString
	 * @return \DateTime
	 */
	public function parseDate($dateString)
	{
		if ($dateString === null || $dateString === "0000-00-00 00:00:00" || $dateString === "0000-00-00" || $dateString === '') {
			return null;
		}
		return new \DateTime($dateString . "+0000");
	}
}


