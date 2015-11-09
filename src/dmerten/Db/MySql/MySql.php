<?php
namespace dmerten\Db\MySql;

use dmerten\Db\Profiler;

/**
 * Class MySql
 *
 * @package dmerten\db\MySql
 */
class MySql
{
	/**
	 * @var Profiler
	 */
	protected $profiler;
	/**
	 * @var string
	 */
	protected $profilerDbTypeStr;
	/**
	 * connection broker for server
	 *
	 * @var ServerConnection
	 */
	protected $connection = null;
	/**
	 * Readonly Flag, prohibits update/insert ... query exec
	 *
	 * @var bool
	 */
	protected $readonly = false;
	/**
	 *
	 * @var string
	 */
	protected $dbname;

	/**
	 * @param ServerConnection $connection
	 * @param string $dbname
	 */
	public function __construct(ServerConnection $connection, $dbname)
	{
		$this->dbname = $dbname;
		$this->connection = $connection;
	}

	/**
	 * @param Profiler $profiler
	 * @param string
	 */
	public function setProfiler(Profiler $profiler, $profilerDbTypeStr)
	{
		$this->profiler = $profiler;
		$this->profilerDbTypeStr = $profilerDbTypeStr;
	}

	/**
	 * @param bool $readonly
	 */
	public function setReadonly($readonly)
	{
		$this->readonly = $readonly;
	}


	/**
	 * as replacement for real_escape in old static class mysql
	 *
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
		return $this->connection->getPdoForDb($this->dbname);
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
	 * true if actually connected, used for unittests
	 *
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


