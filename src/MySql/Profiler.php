<?php
namespace dmerten\Db\MySql;

use dmerten\Db\Profiler as ProfilerInterface;

/**
 *
 * @author Dirk Merten
 */
class Profiler implements ProfilerInterface
{
	/**
	 * @var string
	 */
	protected $currentQuery = '';
	/**
	 * @var int
	 */
	protected $startTimestamp = 0;
	/**
	 * @var array
	 */
	protected $logs = array();
	/**
	 * @var string
	 */
	protected $typeString = '';

	/**
	 * Marks the beginning of a DatabaseCall
	 *
	 * @param string $typString
	 * @param string $sql
	 */
	public function onBeforeCall($typString, $sql)
	{
		$this->currentQuery = $sql;
		$this->typeString = $typString;
		$this->startTimestamp = microtime(true);
	}

	/**
	 * Marks the end of a call, Rows and Cols may be optional
	 */
	public function onAfterCall()
	{
		$this->logs[] = array(
			'database' => $this->typeString,
			'runtime' => (round((microtime(true) - $this->startTimestamp) * 1000, 2)),
			'query' => $this->currentQuery
		);
	}

	/**
	 * @return array
	 */
	public function getLogs()
	{
		return $this->logs;
	}

}
