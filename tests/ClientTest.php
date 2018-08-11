<?php
/**
 * This file is part of the One package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package     One\Redis\Tests
 * @author      Allen Luo <movoin@gmail.com>
 * @since       0.1
 */

namespace One\Redis\Tests;

use One\Redis\Client;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    private $redis;
    private $predis;

    public function setUp()
    {
        $this->connect();
        $this->pconnect();
    }

    public function tearDown()
    {
        $this->redis->close();
        $this->redis = null;
    }

    public function testRedis()
    {
        $redis = $this->redis;

        $redis->set('foo', 'bar');
        $this->assertEquals('bar', $redis->get('foo'));

        $redis->del('foo');
        $this->assertFalse($redis->get('foo'));
    }

    public function testRedisP()
    {
        $redis = $this->predis;

        $redis->set('foo', 'bar');
        $this->assertEquals('bar', $redis->get('foo'));

        $redis->del('foo');
        $this->assertFalse($redis->get('foo'));
    }

    /**
     * @expectedException One\Redis\Exceptions\RedisException
     */
    public function testConnectionException()
    {
        $config = require __DIR__ . '/connect.php';
        $config['port'] = 1024;

        $client = new Client('test', $config);

        $client->get('bad');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAuthException()
    {
        $config = require __DIR__ . '/connect.php';
        $config['password'] = 1024;

        $client = new Client('test', $config);

        $client->get('bad');
    }

    protected function connect()
    {
        if ($this->redis) {
            return $this->redis;
        }

        $config = require __DIR__ . '/connect.php';

        $this->redis = new Client('test', $config);
    }

    protected function pconnect()
    {
        if ($this->predis) {
            return $this->predis;
        }

        $config = require __DIR__ . '/pconnect.php';

        $this->predis = new Client('test', $config);
    }
}
