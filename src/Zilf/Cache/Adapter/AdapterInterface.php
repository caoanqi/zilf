<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zilf\Cache\Adapter;

use Psr\Cache\CacheItemPoolInterface;
use Zilf\Cache\CacheItem;

/**
 * Interface for adapters managing instances of Symfony's {@see CacheItem}.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
interface AdapterInterface extends CacheItemPoolInterface
{
}
