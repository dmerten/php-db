<?php
namespace dmerten\Db;
/**
 * @author Dirk Merten
 */
class DatabaseException extends \Exception
{

	/**
	 * @param string $message
	 * @param null $error
	 * @param null $code
	 * @param \Exception|null $previous
	 */
	public function __construct($message, $error = null, $code = null, \Exception $previous = null)
	{
		if (is_array($error)) {
			$message .= json_encode($error);
		}

		parent::__construct($message, $code, $previous);
	}

}
