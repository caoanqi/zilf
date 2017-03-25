<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zilf\Cache\Tests\Adapter;

use Zilf\Cache\Adapter\PhpFilesAdapter;

/**
 * @group time-sensitive
 */
class PhpFilesAdapterTest extends AdapterTestCase
{
    protected $skippedTests = array(
        'testDefaultLifeTime' => 'PhpFilesAdapter does not allow configuring a default lifetime.',
    );

    public function createCachePool()
    {
        if (!PhpFilesAdapter::isSupported()) {
            $this->markTestSkipped('OPcache extension is not enabled.');
        }

        return new PhpFilesAdapter('sf-cache');
    }

    public static function tearDownAfterClass()
    {
        FilesystemAdapterTest::rmdir(sys_get_temp_dir().'/symfony-cache');
    }
}
