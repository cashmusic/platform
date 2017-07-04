<?php
namespace Kahlan\Spec\Fixture\Plugin\Monkey;

use PDO;

class User
{
    protected $_db = null;

    protected $_success = null;

    public function __construct()
    {
        $this->_db = new PDO(
            "mysql:dbname=testdb;host=localhost",
            'root',
            ''
        );
    }

    public function all()
    {
        $stmt = $this->_db->prepare('SELECT * FROM users');
        $this->_success = $stmt->execute();
        return $stmt->fetchAll();
    }

    public function db()
    {
        return $this->_db;
    }

    public function success()
    {
        return $this->_success;
    }

    public static function create()
    {
        return new static();
    }
}
