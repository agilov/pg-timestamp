<?php

namespace agilov\pgtimestamp\tests;

use PHPUnit\Framework\TestCase;

/**
 * Class TriggerTest
 * vendor/bin/phpunit tests/TriggerTest
 *
 * @author Roman Agilov <agilovr@gmail.com>
 */
final class TriggerTest extends TestCase
{
    /** @var \PDO */
    protected $db;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->db = new \PDO('pgsql:user=postgres dbname=test');
        $this->db->exec('DROP TABLE test');
    }

    /**
     * vendor/bin/phpunit --filter testTimestamp tests/TriggerTest
     */
    public function testTimestamp()
    {
        $this->db->exec('
CREATE TABLE test (
  id SERIAL NOT NULL PRIMARY KEY,
  content TEXT,
  created_at TIMESTAMPTZ
);');
        $this->db->exec("select attach_timestamp_behavior('test', 'created_at', 'INSERT');");
        $this->db->exec("INSERT INTO test (content) VALUES ('test');");

        $raws = $this->db->query("SELECT * FROM test", \PDO::FETCH_ASSOC)->fetchAll();
        $val = $raws[0]['created_at'];
        $this->assertNotEmpty($val);
        $this->assertContains(date('Y-m-d H:i:s'), $val);
    }

    /**
     * vendor/bin/phpunit --filter testUpdate tests/TriggerTest
     */
    public function testUpdate()
    {
        $this->db->exec('
CREATE TABLE test (
  id SERIAL NOT NULL PRIMARY KEY,
  content TEXT,
  updated_at TIMESTAMPTZ
);');
        $this->db->exec("select attach_timestamp_behavior('test', 'updated_at', 'INSERT OR UPDATE');");
        $this->db->exec("INSERT INTO test (content) VALUES ('test');");

        $raws = $this->db->query("SELECT * FROM test", \PDO::FETCH_ASSOC)->fetchAll();
        $val = $raws[0]['updated_at'];
        $this->assertNotEmpty($val);
        $this->assertContains(date('Y-m-d H:i:s'), $val);

        sleep(1);

        $this->db->exec("UPDATE test SET content = 'test2';");
        $raws = $this->db->query("SELECT * FROM test", \PDO::FETCH_ASSOC)->fetchAll();
        $val2 = $raws[0]['updated_at'];
        $this->assertTrue($val2 > $val);
    }

    /**
     * vendor/bin/phpunit --filter testEpoch tests/TriggerTest
     */
    public function testEpoch()
    {
        $this->db->exec('
CREATE TABLE test (
  id SERIAL NOT NULL PRIMARY KEY,
  content TEXT,
  created_at INT
);');

        $this->db->exec("select attach_timestamp_behavior('test', 'created_at', 'INSERT', 'epoch');");
        $this->db->exec("INSERT INTO test (content) VALUES ('test');");

        $raws = $this->db->query("SELECT * FROM test", \PDO::FETCH_ASSOC)->fetchAll();
        $value = $raws[0]['created_at'];

        $this->assertNotEmpty($value);
        $this->assertTrue(is_int($value));

        $this->assertGreaterThan(time() - 3, $value);
        $this->assertLessThan(time() + 3, $value);
    }

    /**
     * vendor/bin/phpunit --filter testFloat tests/TriggerTest
     */
    public function testFloat()
    {
        $this->db->exec('
CREATE TABLE test (
  id SERIAL NOT NULL PRIMARY KEY,
  content TEXT,
  created_at FLOAT
);');

        $this->db->exec("select attach_timestamp_behavior('test', 'created_at', 'INSERT', 'float');");
        $this->db->exec("INSERT INTO test (content) VALUES ('test');");

        $raws = $this->db->query("SELECT * FROM test", \PDO::FETCH_ASSOC)->fetchAll();
        $value = (float)$raws[0]['created_at'];

        $this->assertNotEmpty($value);
        $this->assertGreaterThan(time() - 3, $value);
        $this->assertLessThan(time() + 3, $value);
    }
}
