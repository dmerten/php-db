<?php
namespace dmerten\db\MySql;

class Config
{
	/**
	 * pdo dsn: e.g. "mysql:host=192.168.15.16;port=3306"
	 *
	 * @var string
	 */
	public $dsn;
	/**
	 * @var string
	 */
	public $database;
	/**
	 * @var string
	 */
	public $username;
	/**
	 * @var string
	 */
	public $password;
	/**
	 * @var bool
	 */
	public $readonly = false;
}
