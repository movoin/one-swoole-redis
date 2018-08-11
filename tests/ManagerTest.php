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

use One\Redis\Manager;

class ManagerTest extends \PHPUnit\Framework\TestCase
{
    protected $manager;

    protected $configs = [
        'test' => [
            'database' => ['session' => 0, 'cache' => 1]
        ]
    ];

    public function setUp()
    {
        $this->manager = new Manager($this->configs);
    }

    public function tearDown()
    {
        $this->manager = null;
    }

    public function testGet()
    {
        $this->assertInstanceOf('One\Redis\Client', $this->manager->getClient('test'));
        $this->assertInstanceOf('One\Redis\Client', $this->manager->test);
    }

    public function testAttach()
    {
        $this->manager->attach(
            'foo',
            [
                'database' => [
                    'demo' => 0,
                    'cache' => 1
                ]
            ]
        );
        $this->manager->foo->demo->set('foo', 'bar');
        $this->assertEquals('bar', $this->manager->foo->demo->get('foo'));

        $this->manager->foo->demo->del('foo');
        $this->assertFalse($this->manager->foo->demo->get('foo'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAttachException()
    {
        $this->manager->attach('test', []);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetException()
    {
        $this->manager->bad;
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetDatabaseException()
    {
        $this->manager->test->bad;
    }
}
