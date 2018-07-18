<?php

namespace agilov\pgtimestamp\tests;

use PHPUnit\Framework\TestCase;

/**
 * Class CombineTest
 * vendor/bin/phpunit tests/CombineTest
 *
 * @author Roman Agilov <agilovr@gmail.com>
 */
final class CombineTest extends TestCase
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
     * vendor/bin/phpunit --filter testCombineCreatedAndUpdated tests/CombineTest
     */
    public function testCombineCreatedAndUpdated()
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


        $this->db->exec("INSERT INTO test (content) VALUES ('test');");

        $raws = $this->db->query("SELECT * FROM test", \PDO::FETCH_ASSOC)->fetchAll();
        $createdAt = $raws[0]['created_at'];
        $this->assertNotEmpty($createdAt);
        $this->assertContains(date('Y-m-d H:i:s'), $createdAt);


        $raws = $this->db->query("SELECT * FROM test", \PDO::FETCH_ASSOC)->fetchAll();
        $updatedAt = $raws[0]['updated_at'];
        $this->assertNotEmpty($updatedAt);
        $this->assertContains(date('Y-m-d H:i:s'), $updatedAt);

        sleep(1);

        $this->db->exec("UPDATE test SET content = 'test2';");
        $raws = $this->db->query("SELECT * FROM test", \PDO::FETCH_ASSOC)->fetchAll();
        $updatedAt2 = $raws[0]['updated_at'];
        $this->assertTrue($updatedAt2 > $updatedAt);
    }
}
