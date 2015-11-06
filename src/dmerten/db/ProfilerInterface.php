<?php
namespace dmerten\db;

/**
 * @author Dirk Merten
 */
interface ProfilerInterface
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
