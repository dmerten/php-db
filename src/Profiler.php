<?php
namespace dmerten\Db;

/**
 * @author Dirk Merten
 */
interface Profiler
{
	/**
	 * Marks the beginning of a DatabaseCall
	 *
	 * @param string $typString
	 * @param string $sql actual call string
	 */
	public function onBeforeCall($typString, $sql);

	/**
	 * Marks the end of a call, Rows and Cols may be optional
	 */
	public function onAfterCall();
}
