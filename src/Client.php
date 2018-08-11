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
     * 客户端名称
     *
     * @var string
     */
    protected $name;
    /**
     * 客户端配置信息
     *
     * @var array
     */
    protected $config = [];

    /**
     * Redis 客户端实例
     *
     * @var \Redis
     */
    private $redis;

    /**
     * 构造
     *
     * @param array $config
     */
    public function __construct(string $name, array $config)
    {
        $this->name = $name;
        $this->config = $config;
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

        if ($this->config['persistent'] === true) {
            $connect = 'pconnect';
            $args = [
                $this->config['host'],
                $this->config['port'],
                $this->config['timeout'],
                $this->name,
                $this->config['retryInterval'],
                $this->config['readTimeout']
            ];
        } else {
            $connect = 'connect';
            $args = [
                $this->config['host'],
                $this->config['port'],
                $this->config['timeout'],
                null,
                $this->config['retryInterval'],
                $this->config['readTimeout']
            ];
        }

        try {
            call_user_func_array([$this->redis, $connect], $args);
        } catch (\RedisException $e) {
            throw new RedisException($e->getMessage(), $e->getCode(), $e);
        }

        unset($connect, $args);

        if ($this->config['password'] !== null && ! $this->redis->auth($this->config['password'])) {
            throw new InvalidArgumentException('The Redis password is wrong');
        }

        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);

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

    /**
     * 获得指定数据库的客户端
     *
     * @param string $database
     *
     * @return \One\Redis\Client
     * @throws \InvalidArgumentException
     */
    public function __get($database): self
    {
        if (! isset($this->config['database'][$database])) {
            throw new InvalidArgumentException(
                sprintf('The database was not found: "%s"', $database)
            );
        }

        $clone = clone $this;

        if ($clone->redis === null) {
            $clone->connect();
        }

        $clone->redis->select((int) $this->config['database'][$database]);

        return $clone;
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
}
