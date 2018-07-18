<?php

namespace agilov\pgtimestamp\tests;

use PHPUnit\Framework\TestCase;

/**
 * Class PerformanceTest
 * vendor/bin/phpunit tests/PerformanceTest
 *
 * @author Roman Agilov <agilovr@gmail.com>
 */
final class PerformanceTest extends TestCase
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
     * vendor/bin/phpunit --filter testPerformance tests/PerformanceTest
     */
    public function testPerformance()
    {
        $this->db->exec('
CREATE TABLE test (
  id SERIAL NOT NULL PRIMARY KEY,
  content TEXT,
  created_at TIMESTAMPTZ,
  updated_at TIMESTAMPTZ
);');


        $this->db->exec("select attach_timestamp_behavior('test', 'created_at', 'INSERT');");
        $this->db->exec("select attach_timestamp_behavior('test', 'updated_at', 'INSERT OR UPDATE');");

        $start = time();

        for ($i = 0; $i < 1000; $i++) {
            $this->db->exec("INSERT INTO test (content) VALUES ('test');");
        }

        $this->assertTrue(time() - $start < 2);
    }
}
