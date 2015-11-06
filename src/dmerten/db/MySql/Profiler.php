<?php
namespace dmerten\db\MySql;

use dmerten\db\ProfilerInterface;

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
	 * @param string $typString e.g. my, ora, myM, ...
	 * @param string $sql actual callstring
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
			'runtime' => (round((microtime(true) - $this->startTimestamp) * 1000, 2)) . " ms",
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
