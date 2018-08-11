<?php
/**
 * This file is part of the One package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package     One\Redis
 * @author      Allen Luo <movoin@gmail.com>
 * @since       0.2
 */

namespace One\Redis;

use InvalidArgumentException;

/**
 * Redis 连接管理器
 *
 * ```
 * $redis = new Manager([
 *     'local' => [
 *         'port'       => 6380,
 *         'database'   => ['session' => 0, 'cache' => 1]
 *     ],
 *     'cloud' => [
 *         'host'       => '10.0.0.2',
 *         'database'   => ['etc' => 0, 'demo' => 1]
 *     ],
 *     'test' => [
 *         'host'       => '10.0.0.3'
 *     ]
 * ]);
 *
 * // 默认数据库 select(0)
 * $redis->local->set('key', 'value');
 *
 * // 指定数据库
 * $redis->local->cache->set('key', 'value');
 * ```
 *
 * @package     One\Redis
 * @author      Allen Luo <movoin@gmail.com>
 * @since       0.2
 */
class Manager
{
    /**
     * Redis 客户端
     *
     * @var array
     */
    private $clients = [];
    /**
     * 连接配置
     *
     * @var array
     */
    private $configs = [];
    /**
     * 默认配置信息
     *
     * @var array
     */
    private $defaults = [
        'host'          => '127.0.0.1',
        'port'          => 6379,
        'database'      => ['default' => 0],
        'prefix'        => null,
        'password'      => null,
        'timeout'       => 3,
        'readTimeout'   => 1,
        'retryInterval' => 100,
        'persistent'    => false,
    ];

    /**
     * 构造
     *
     * ```
     * $manager = new Manager([
     *     'local' => [
     *         'port'       => 6380,
     *         'database'   => ['session' => 0, 'cache' => 1]
     *     ],
     *     'cloud' => [
     *         'host'       => '10.0.0.2',
     *         'database'   => ['etc' => 0, 'demo' => 1]
     *     ],
     *     'test' => [
     *         'host'       => '10.0.0.3'
     *     ]
     * ]);
     * ```
     *
     * @param array $configs
     */
    public function __construct(array $configs = [])
    {
        $this->configure($configs);
    }

    /**
     * 绑定新的客户端
     *
     * @param string $prefix
     * @param array  $config
     */
    public function attach(string $prefix, array $config = [])
    {
        $prefix = $this->getPrefix($prefix);

        if (isset($this->configs[$prefix])) {
            throw new InvalidArgumentException(
                sprintf('The client was already exist: "%s"', $prefix)
            );
        }

        $this->configs[$prefix] = array_merge($this->defaults, $config);
        $this->clients[$prefix] = new Client($prefix, $this->configs[$prefix]);
    }

    /**
     * 获得客户端
     *
     * @param string $prefix
     *
     * @return \One\Redis\Client
     */
    public function getClient(string $prefix): Client
    {
        $prefix = $this->getPrefix($prefix);

        if (! isset($this->configs[$prefix])) {
            throw new InvalidArgumentException(
                sprintf('The client was not found: "%s"', $prefix)
            );
        }

        if (! isset($this->clients[$prefix])) {
            $this->clients[$prefix] = new Client($prefix, $this->configs[$prefix]);
        }

        return $this->clients[$prefix];
    }

    /**
     * 获得 Redis Client 实例
     *
     * @param string $prefix
     *
     * @return \One\Redis\Client
     * @throws \InvalidArgumentException
     */
    public function __get($prefix): Client
    {
        return $this->getClient($prefix);
    }

    /**
     * 初始化配置
     *
     * @param array $configs
     */
    protected function configure(array $configs)
    {
        foreach ($configs as $prefix => $config) {
            $prefix = $this->getPrefix($prefix);
            $this->configs[$prefix] = array_merge($this->defaults, $config);
        }
    }

    /**
     * 获得前缀
     *
     * @param string $prefix
     *
     * @return string
     */
    private function getPrefix(string $prefix): string
    {
        return strtolower($prefix);
    }
}
