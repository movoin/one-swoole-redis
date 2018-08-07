<?php
/**
 * This file is part of the One package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package     One\Redis
 * @author      Allen Luo <movoin@gmail.com>
 * @since       0.1
 */

namespace One\Redis;

use Redis;
use InvalidArgumentException;
use One\Redis\Exceptions\RedisException;

/**
 * Redis 客户端
 *
 * @package     One\Redis
 * @author      Allen Luo <movoin@gmail.com>
 * @since       0.1
 */
class Client
{
    /**
     * 配置信息
     *
     * @var array
     */
    protected $config = [
        'host'          => '127.0.0.1',
        'port'          => 6379,
        'database'      => null,
        'prefix'        => null,
        'password'      => null,
        'timeout'       => 3,
        'readTimeout'   => 1,
        'retryInterval' => 100,
        'persistent'    => false,
        'persistentId'  => 'redis'
    ];

    /**
     * Redis 客户端
     *
     * @var \Redis
     */
    private $redis;

    /**
     * 构造
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge(
            $this->config,
            $config
        );
    }

    /**
     * 映射 Redis 方法
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->connect(), $method], $args);
    }

    /**
     * 连接 Redis
     *
     * @return \Redis
     */
    public function connect()
    {
        if ($this->redis !== null) {
            return $this->redis;
        }

        $this->redis = new Redis;
        $config = $this->config;

        if ($config['persistent'] === true) {
            $connect = 'pconnect';
            $args = [
                $config['host'],
                $config['port'],
                $config['timeout'],
                $config['persistentId'],
                $config['retryInterval'],
                $config['readTimeout']
            ];
        } else {
            $connect = 'connect';
            $args = [
                $config['host'],
                $config['port'],
                $config['timeout'],
                null,
                $config['retryInterval'],
                $config['readTimeout']
            ];
        }

        try {
            call_user_func_array([$this->redis, $connect], $args);
        } catch (\RedisException $e) {
            throw new RedisException($e->getMessage(), $e->getCode(), $e);
        }

        unset($connect, $args);

        if ($config['password'] !== null && ! $this->redis->auth($config['password'])) {
            throw new InvalidArgumentException('The Redis password is wrong');
        }

        if ($config['database'] !== null) {
            if (is_int($config['database'])) {
                $this->redis->select($config['database']);
            } else {
                throw new InvalidArgumentException('Database expects to be integer');
            }
        }

        if ($config['prefix'] !== null) {
            $this->redis->setOption(Redis::OPT_PREFIX, $config['prefix']);
        }

        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);

        unset($config);

        return $this->redis;
    }

    /**
     * 断开 Redis 连接
     */
    public function close()
    {
        if ($this->redis instanceof Redis) {
            if ($this->config['persistent'] === false) {
                $this->redis->close();
            }
            $this->redis = null;
        }
    }
}
