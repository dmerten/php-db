<?php
namespace dmerten\Db\Exception;
use dmerten\Db\DatabaseException;

/**
 * Thrown of execute detects a duplicate key exception
 * currently only mysql
 */
class DuplicateKey extends DatabaseException
{

}
