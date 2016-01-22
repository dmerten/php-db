<?php
/**
 *
 * @author Dirk Merten
 */

include 'vendor/autoload.php';

// Configure database factory
$configs = new \dmerten\Db\Config();
$configs->addMysql('my_database', 'database_name', 'localhost', 'root', '', true, true);
$dbFactory = new \dmerten\Db\Factory($configs);

// Get MySql connection
$mySql = $dbFactory->getMysql('my_database');

// Simple query with one result value
$mySql->query('SELECT 1 FROM dual')->fetchValue();

// Query with binding
$mySql->query('SELECT * FROM dual WHERE fieldname_1 = :PARAM_1 AND fieldname_2 = :PARAM_2')
	->bindInt('PARAM_1', '1337')
	->bindString('PARAM_2', 'foo')
	->fetchAll();

