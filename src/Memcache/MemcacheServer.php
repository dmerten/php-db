<?php
namespace dmerten\Db\Memcache;

/**
 *
 * @author Dirk Merten
 */
class MemcacheServer
{
	/**
	 * @var string
	 */
	protected $host = '';
	/**
	 * @var int
	 */
	protected $port = 0;

	/**
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * @param string $host
	 */
	public function setHost($host)
	{
		$this->host = (string)$host;
	}

	/**
	 * @return int
	 */
	public function getPort()
	{
		return $this->port;
	}

	/**
	 * @param int $port
	 */
	public function setPort($port)
	{
		$this->port = (int)$port;
	}

}
