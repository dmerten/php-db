<?php
namespace dmerten\Db\MySql;

use dmerten\Db\Exception\DuplicateKey;
use dmerten\Db\Exception\MySql;
use dmerten\Db\ProfilerInterface;

class Query
{
	const SLOW_QUERY_TIME_LIMIT = 60000; // limit in ms. default: 3000. 0 to disable
	/**
	 * SQL Query String
	 *
	 * @var string
	 */
	protected $sql;
	/**
	 * Db Connection
	 *
	 * @var \PDO
	 */
	protected $connection;
	/**
	 * Prepared Statement Ressource from PDO::prepare
	 *
	 * @var \PDOStatement
	 */
	protected $stmt;
	/**
	 * Flag to prohibit double execution
	 *
	 * @var bool
	 */
	protected $executed = false;
	/**
	 * save values to bind on execute
	 * $binds['varname'] = array(
	 *        'value' => 'theBindValue',
	 *        'type' => PDO::PARAM_*
	 * )
	 *
	 * @var array
	 */
	protected $binds = array();
	/**
	 * @var ProfilerInterface
	 */
	protected $profiler;
	/**
	 * @var string
	 */
	protected $profilerDbTypeStr;
	/**
	 * Readonly Flag, prohibits update/insert ... query exec
	 *
	 * @var bool
	 */
	protected $readonly = false;
	/**
	 * @var float
	 */
	protected $slowQueryStartTimestamp = 0.0;
	/**
	 * @var int
	 */
	protected $arrayCounter = 0;

	/**
	 * constructor +executes oci_parse
	 *
	 * @param \PDO|resource $connection
	 * @param string $sql
	 */
	public function __construct(\PDO $connection, $sql)
	{
		$this->sql = $sql;
		$this->connection = $connection;
	}

	/**
	 * @param ProfilerInterface $profiler
	 * @param $profilerDbTypeStr
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
		$this->readonly = $readonly;
	}

	/**
	 * Bind parameter $key with $value
	 * Binds are stored and bound on query execute
	 *
	 * @param string $key
	 * @param int|string $value
	 * @param int $type PDO::PARAM_*
	 * @return Query
	 */
	protected function bind($key, $value, $type = \PDO::PARAM_STR)
	{
		$this->binds[":" . $key] = array(
			'value' => $value,
			'type' => $type
		);
		return $this;
	}

	/**
	 * @param string $key
	 * @param array $value
	 * @return array
	 */
	protected function bindArray($key, array $value)
	{
		$bindArray = array();
		foreach ($value as $val) {
			$bindArray['arrayparam' . $this->arrayCounter] = $val;
			$this->arrayCounter++;
		}

		$keys = ":" . implode(', :', array_keys($bindArray));
		$this->sql = preg_replace("/(:$key)/u", $keys, $this->sql, 1);

		return $bindArray;
	}

	/**
	 * @param string $key
	 * @param int[] $value
	 * @return Query
	 */
	public function bindIntArray($key, array $value)
	{
		$bindArray = $this->bindArray($key, $value);
		foreach ($bindArray as $bindKey => $bindValue) {
			$this->bindInt($bindKey, $bindValue);
		}

		return $this;
	}

	/**
	 * @param string $key
	 * @param string[] $value
	 * @return Query
	 */
	public function bindStringArray($key, array $value)
	{
		$bindArray = $this->bindArray($key, $value);
		foreach ($bindArray as $bindKey => $bindValue) {
			$this->bindString($bindKey, $bindValue);
		}

		return $this;
	}

	/**
	 * Bind Int Parameter to Query
	 *
	 * @param string $key
	 * @param int|null $value
	 * @throws \InvalidArgumentException
	 * @return Query
	 */
	public function bindInt($key, $value)
	{
		if ($value !== null && !is_numeric($value)) {
			throw new \InvalidArgumentException("bind Int expects int or at least a numeric value");
		}
		return $this->bind($key, $value, \PDO::PARAM_INT);
	}

	/**
	 * Bind String Parameter to Query
	 * Encodes automatically to UTF-8 if it's not a valid UTF-8 string
	 *
	 * @param string $key
	 * @param string $value
	 * @return Query
	 */
	public function bindString($key, $value)
	{
		if (!mb_check_encoding($value, 'UTF-8')) {
			$value = utf8_encode($value);
			trigger_error('UTF8: need encoding in MySQL query function bindString()');
		}
		return $this->bind($key, $value);
	}

	/**
	 * Bind String Parameter to Query and cast NULL to empty string
	 * Encodes automatically to UTF-8 if it's not a valid UTF-8 string
	 *
	 * @param string $key
	 * @param string $value
	 * @return Query
	 */
	public function bindStringNotNull($key, $value)
	{
		return $this->bindString($key, (string)$value);
	}

	/**
	 * Bind Decimal Parameter to Query
	 *
	 * @param string $key
	 * @param float $value
	 * @return Query
	 */
	public function bindDecimal($key, $value)
	{
		return $this->bind($key, $value);
	}

	/**
	 * Bind Blob Parameter to Query without convertions
	 *
	 * @param string $key
	 * @param string $value
	 * @return Query
	 */
	public function bindBlob($key, $value)
	{
		return $this->bind($key, $value);
	}

	/**
	 * Will append "LIMIT" to the query
	 * Should be called directly before fetch*()
	 * CAUTION: Breaks the query if there is already a LIMIT in it!
	 *
	 * @param int $param1 If $param2 is set, then its the offset else the row_count
	 * @param int $param2 The row_count
	 * @return Query
	 */
	public function limit($param1, $param2 = null)
	{
		if ($param2 === null) {
			$param2 = $param1;
			$param1 = 0;
		}
		$this->sql .= " LIMIT $param1, $param2";

		return $this;
	}

	/**
	 * Achtung: Die Rückgabe kann sich von dem tatsächlichen Statment unterscheiden!!!!!!!!!!!
	 *
	 * @return string
	 */
	public function getSQL()
	{
		$sql = preg_replace("/(:\w+[\d\w]*)/ui", '$1tmp', $this->sql);
		foreach ($this->binds as $bindName => $bindParams) {
			$sql = str_replace($bindName . 'tmp', $this->connection->quote($bindParams['value'], $bindParams['type']), $sql);
		}

		return $sql;
	}

	/**
	 * execute the query /oci_execute
	 *
	 * @throws MySql
	 * @throws DuplicateKey
	 * @throws \Exception
	 */
	protected function exec()
	{
		if ($this->executed) {
			throw new \Exception("Query already executed, setReuse not yet implemented");
		}

		if ($this->profiler) {
			$this->profiler->onBeforeCall($this->profilerDbTypeStr, $this->getSQL());
		}

		// slow query log
		if ((self::SLOW_QUERY_TIME_LIMIT > 0)) {
			$this->slowQueryStartTimestamp = microtime(true);
		}

		try {
			$this->sql = preg_replace("/(:\w+[\d\w]*)/ui", '$1tmp', $this->sql);
			$this->stmt = $this->connection->prepare($this->sql);
			foreach ($this->binds as $bindName => $bindParams) {
				$this->stmt->bindValue($bindName . 'tmp', $bindParams['value'], $bindParams['type']);
			}
			$this->stmt->execute();
		} catch (\PDOException $ex) {
			if ($ex->getCode() == "23000") {
				if (mb_strpos($ex->getMessage(), " 1062 ")) {
					throw new DuplicateKey("Duplicate Key " . $ex, null, null, $ex);
				}
			}
			throw new MySql("Query failed " . $ex, null, null, $ex);
		}
	}

	/**
	 * free connection resources
	 * called by fetch** call
	 * save to be called multiple times
	 */
	protected function cleanup()
	{
		$this->executed = true;
		$this->stmt = null;
		$this->binds = array();
		if ($this->profiler) {
			$this->profiler->onAfterCall();
		}

		// slow query log
		if ($this->slowQueryStartTimestamp && (($queryTime = (microtime(true) - $this->slowQueryStartTimestamp) * 1000) >= self::SLOW_QUERY_TIME_LIMIT)) {
			trigger_error('Slow MySQL query detected (' . (int)$queryTime . 'ms): ' . $this->getSQL(), E_USER_WARNING);
		}

	}

	/**
	 * Execute Query and return Result as Two Dimensional Array
	 *
	 * @throws MySql
	 * @return array
	 */
	public function fetchAll()
	{
		$this->exec();
		$return = $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
		$this->cleanup();
		return $return;
	}

	/**
	 * Execute Query and return value of first column of first row
	 * Returns NULL if no rows are returned
	 *
	 * @return null|string
	 */
	public function fetchValue()
	{
		$this->exec();
		$return = $this->stmt->fetchColumn();
		$this->cleanup();
		return ($return === false) ? null : $return;
	}

	/**
	 * Execute Query and return value of first column of first row
	 * Returns NULL if no rows are returned
	 *
	 * @return null|string
	 */
	public function fetchRow()
	{
		$this->exec();
		$temp = $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
		$return = null;
		$this->cleanup();

		if (count($temp) >= 1) {
			$return = $temp[0];
		}

		return $return;
	}

	/**
	 * Execute Query and return array of (firstcolumn=>secondcolumn, firstcolumn=>secondcolumn, ...)
	 * Returns array() if no rows are returned
	 *
	 * @return array
	 */
	public function fetchPair()
	{
		$this->exec();
		$resX = $this->stmt->fetchAll(\PDO::FETCH_NUM);
		$return = array();
		foreach ($resX as $row) {
			$return[$row[0]] = $row[1];
		}

		$this->cleanup();
		return $return;
	}

	/**
	 * @param int $n
	 * @throws \OutOfRangeException
	 * @return array
	 */
	public function fetchTree($n = 1)
	{
		$this->exec();
		$resX = $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
		$return = array();
		$this->cleanup();

		if (count($resX) == 0) {
			return $return;
		}

		$colNames = array_keys($resX[0]);
		foreach ($resX as $row) {
			switch ($n) {
				case 1:
					$return[$row[$colNames[0]]] = $row;
					break;
				case 2:
					$return[$row[$colNames[0]]][$row[$colNames[1]]] = $row;
					break;
				case 3:
					$return[$row[$colNames[0]]][$row[$colNames[1]]][$row[$colNames[2]]] = $row;
					break;
				case 4:
					$return[$row[$colNames[0]]][$row[$colNames[1]]][$row[$colNames[2]]][$row[$colNames[4]]] = $row;
					break;
				default:
					throw new \OutOfRangeException("Too many Level of Hierarchy ($n)");
			}
		}
		return $return;
	}

	/**
	 * Handler f?r Update, Delete, Replace
	 * return affected rows
	 *
	 * @return int
	 */
	protected function execute()
	{
		$this->exec();
		$return = $this->stmt->rowCount();
		$this->cleanup();
		return $return;
	}

	/**
	 * Update
	 * return affected (updated rows)
	 *
	 * @throws \LogicException
	 * @return int
	 */
	public function update()
	{
		if ($this->readonly) {
			throw new \LogicException("Cannot Update ReadOnly Database");
		}
		return $this->execute();
	}

	/**
	 * Delete
	 * return affected (updated rows)
	 *
	 * @throws \LogicException
	 * @return int
	 */
	public function delete()
	{
		if ($this->readonly) {
			throw new \LogicException("Cannot Update ReadOnly Database");
		}
		return $this->execute();
	}

	/**
	 * Replace
	 * return affected (updated rows)
	 *
	 * @throws \LogicException
	 * @return int
	 */
	public function replace()
	{
		if ($this->readonly) {
			throw new \LogicException("Cannot Update ReadOnly Database");
		}
		return $this->execute();
	}

	/**
	 * @return int
	 * @throws DuplicateKey
	 * @throws MySql
	 * @throws \Exception
	 */
	public function insert()
	{
		if ($this->readonly) {
			throw new \LogicException("Cannot Update ReadOnly Database");
		}
		$this->exec();
		$return = (int)$this->connection->lastInsertId();
		$this->cleanup();
		return $return;
	}

	/**
	 * Execute Query and return array of first column
	 * Returns array() if no rows are returned
	 *
	 * @return array
	 */
	public function fetchColumn()
	{
		$this->exec();
		$resX = $this->stmt->fetchAll(\PDO::FETCH_NUM);
		$this->cleanup();
		$return = array();
		foreach ($resX as $row) {
			$return[] = $row[0];
		}
		return $return;
	}

}
