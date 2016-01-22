<?php
namespace dmerten\Db\MySql;

use dmerten\Db\Exception\MySqlException;

class ServerConnection
{
	/**
	 * Db username
	 *
	 * @var string
	 */
	private $user;
	/**
	 * Db password
	 *
	 * @var string
	 */
	private $pwd;
	/**
	 * dsn name or connection String
	 *
	 * @var string
	 */
	private $dsn;
	/**
	 * Link Ressource
	 *
	 * @var \PDO
	 */
	private $pdo = null;
	/**
	 * Incremented for every "use db" call
	 *
	 * @var int
	 */
	private $dbChangedCounter = 0;
	/**
	 * Last Database Name
	 *
	 * @var string
	 */
	private $lastDbName = null;

	/**
	 * Construct the Server Connection
	 *
	 * @param string $user
	 * @param string $pwd
	 * @param string $dsn
	 */
	public function __construct($user, $pwd, $dsn)
	{
		$this->user = $user;
		$this->pwd = $pwd;
		$this->dsn = $dsn;
	}

	/**
	 * true if actually connected, used for unittests
	 *
	 * @return bool
	 */
	public function isConnected()
	{
		return ($this->pdo !== null);
	}

	/**
	 * connects to db or returnes a recycled pdo, if the link is used for multiple databases "use database" is executed
	 * this pdo object should not be cached locally !!!
	 *
	 * @param string $dbname
	 * @return \PDO
	 * @throws MySqlException
	 */
	public function getPdoForDb($dbname)
	{
		$this->connect();
		if ($this->lastDbName === null || $this->lastDbName != $dbname) {
			try {
				$this->pdo->exec("use `$dbname`");
				// @codeCoverageIgnoreStart
				$this->dbChangedCounter++;
				// @codeCoverageIgnoreEnd
				$this->lastDbName = $dbname;
			} catch (\PDOException $ex) {
				$this->lastDbName = null;
				throw new MySqlException("Use Database '{$dbname}' failed", null, null, $ex);
			}
		}
		return $this->pdo;
	}

	/**
	 * connect to database, or do nothing if already connected
	 *
	 * @throws MySqlException
	 */
	private function connect()
	{
		if ($this->pdo === null) {
			$pdoOptions = array(
				\PDO::ATTR_PERSISTENT => true,
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_PREFETCH => 1000,
				\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
			);
			$this->dsn .= ';charset=utf8';
			try {
				$this->pdo = new \PDO($this->dsn, $this->user, $this->pwd, $pdoOptions);
			} catch (\PDOException $ex) {
				throw new MySqlException("Connect to '{$this->dsn}' with user '{$this->user}' failed", null, null, $ex);
			}
		}
	}

	/**
	 * @return int
	 */
	public function getDbChangedCounter()
	{
		return $this->dbChangedCounter;
	}

}
