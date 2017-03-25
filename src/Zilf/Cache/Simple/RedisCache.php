<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zilf\Cache\Simple;

use Zilf\Cache\Traits\RedisTrait;

class RedisCache extends AbstractCache
{
    use RedisTrait;

    /**
     * @param \Redis|\RedisArray|\RedisCluster|\Predis\Client $redisClient
     */
    public function __construct($redisClient, $namespace = '', $defaultLifetime = 0)
    {
        $this->init($redisClient, $namespace, $defaultLifetime);
    }
}