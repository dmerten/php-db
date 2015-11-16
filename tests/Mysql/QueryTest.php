<?php

/**
 *
 * @author Dirk Merten
 */
class QueryTest extends PHPUnit_Framework_TestCase {

	public function testGetSQL() {
		$query = new \dmerten\Db\MySql\Query(new PDOMock(), 'SELECT 1 FROM dual');
		$this->assertSame('SELECT 1 FROM dual', $query->getSQL());
	}

	public function testBindInt() {
		$query = new \dmerten\Db\MySql\Query(new PDOMock(), 'SELECT * FROM dual WHERE foo = :BAR');
		$query->bindInt('BAR', 1337);
		$this->assertSame('SELECT * FROM dual WHERE foo = 1337', $query->getSQL());
	}

	public function testLimit() {
		$query = new \dmerten\Db\MySql\Query(new PDOMock(), 'SELECT * FROM dual');
		$query->limit(0, 10);
		$this->assertSame('SELECT * FROM dual LIMIT 0, 10', $query->getSQL());

		$query = new \dmerten\Db\MySql\Query(new PDOMock(), 'SELECT * FROM dual');
		$query->limit(5);
		$this->assertSame('SELECT * FROM dual LIMIT 0, 5', $query->getSQL());
	}

	public function testBindStringArray() {
		$query = new \dmerten\Db\MySql\Query(new PDOMock(), 'SELECT * FROM dual WHERE bar IN (:FOO)');
		$query->bindStringArray('FOO', array('a', 'b'));
		$this->assertSame('SELECT * FROM dual WHERE bar IN (a, b)', $query->getSQL());
	}

	public function testReadOnly() {
		$this->setExpectedException('LogicException');
		$query = new \dmerten\Db\MySql\Query(new PDOMock(), 'INSERT INTO dual VALUES (1,2,3)');
		$query->setReadonly(true);
		$query->insert();
	}

}

class PDOMock extends \PDO {
	/**
	 * Dummy fuers Testen
	 */
	public function __construct() {
	}

	/**
	 * Mock der nicht escaped
	 *
	 * @param string $param
	 * @param null $paramtype
	 * @return string
	 */
	public function quote($param, $paramtype = null) {
		return $param;
	}
}
