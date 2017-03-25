<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zilf\Cache\Traits;

use Zilf\Cache\Exception\CacheException;
use Zilf\Cache\Exception\InvalidArgumentException;

/**
 * @author Piotr Stankowski <git@trakos.pl>
 * @author Nicolas Grekas <p@tchwork.com>
 *
 * @internal
 */
trait PhpFilesTrait
{
    use FilesystemCommonTrait;

    private $includeHandler;

    public static function isSupported()
    {
        return function_exists('opcache_compile_file') && ini_get('opcache.enable');
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch(array $ids)
    {
        $values = array();
        $now = time();

        set_error_handler($this->includeHandler);
        try {
            foreach ($ids as $id) {
                try {
                    $file = $this->getFile($id);
                    list($expiresAt, $values[$id]) = include $file;
                    if ($now >= $expiresAt) {
                        unset($values[$id]);
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        } finally {
            restore_error_handler();
        }

        foreach ($values as $id => $value) {
            if ('N;' === $value) {
                $values[$id] = null;
            } elseif (is_string($value) && isset($value[2]) && ':' === $value[1]) {
                $values[$id] = parent::unserialize($value);
            }
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    protected function doHave($id)
    {
        return (bool) $this->doFetch(array($id));
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave(array $values, $lifetime)
    {
        $ok = true;
        $data = array($lifetime ? time() + $lifetime : PHP_INT_MAX, '');
        $allowCompile = 'cli' !== PHP_SAPI || ini_get('opcache.enable_cli');

        foreach ($values as $key => $value) {
            if (null === $value || is_object($value)) {
                $value = serialize($value);
            } elseif (is_array($value)) {
                $serialized = serialize($value);
                $unserialized = parent::unserialize($serialized);
                // Store arrays serialized if they contain any objects or references
                if ($unserialized !== $value || (false !== strpos($serialized, ';R:') && preg_match('/;R:[1-9]/', $serialized))) {
                    $value = $serialized;
                }
            } elseif (is_string($value)) {
                // Serialize strings if they could be confused with serialized objects or arrays
                if ('N;' === $value || (isset($value[2]) && ':' === $value[1])) {
                    $value = serialize($value);
                }
            } elseif (!is_scalar($value)) {
                throw new InvalidArgumentException(sprintf('Cache key "%s" has non-serializable %s value.', $key, gettype($value)));
            }

            $data[1] = $value;
            $file = $this->getFile($key, true);
            $ok = $this->write($file, '<?php return '.var_export($data, true).';') && $ok;

            if ($allowCompile) {
                @opcache_compile_file($file);
            }
        }

        if (!$ok && !is_writable($this->directory)) {
            throw new CacheException(sprintf('Cache directory is not writable (%s)', $this->directory));
        }

        return $ok;
    }
}