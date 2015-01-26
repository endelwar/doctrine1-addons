<?php
/*
 *  $Id: Memcache.php 7490 2010-03-29 19:53:27Z jwage $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

/**
 * Memcached cache driver
 *
 * @package     Doctrine
 * @subpackage  Cache
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision: 7490 $
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Doctrine_Cache_Memcached extends Doctrine_Cache_Driver
{
    /**
     * @var Memcached $_memcache     memcache object
     */
    protected $_memcached = null;

    /**
     * constructor
     *
     * @param array $options        associative array of cache driver options
     */
    public function __construct($options = array())
    {
        if ( ! extension_loaded('memcached')) {
            throw new Doctrine_Cache_Exception('In order to use Memcached driver, the memcached extension must be loaded.');
        }
        parent::__construct($options);

        if (isset($options['servers'])) {
            $value = $options['servers'];
            if (isset($value['host'])) {
                // in this case, $value seems to be a simple associative array (one server only)
                $value = array(0 => $value); // let's transform it into a classical array of associative arrays
            }
            $this->setOption('servers', $value);
        }
        if (!isset($options['persistent_id'])) {
            $this->_memcached = new Memcached();
        } else {
            $this->_memcached = new Memcached($options['persistent_id']);
        }

        $this->_memcached->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);

        if (true == $this->_options['compression']) {
            $this->_memcached->setOption(Memcached::OPT_COMPRESSION, true);
        } else {
            $this->_memcached->setOption(Memcached::OPT_COMPRESSION, false);
        }

        foreach ($this->_options['servers'] as $server) {
            if (!array_key_exists('port', $server)) {
                $server['port'] = 11211;
            }
            $this->_memcached->addServer($server['host'], $server['port']);
        }
    }

    /**
     * Test if a cache record exists for the passed id
     *
     * @param string $id cache id
     * @return mixed  Returns either the cached data or false
     */
    protected function _doFetch($id, $testCacheValidity = true)
    {
        if (false == $this->_memcached->getOption(Memcached::OPT_BINARY_PROTOCOL)) {
            $id = str_replace(' ', '_', $id);
        }
        return $this->_memcached->get($id);
    }

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param string $id cache id
     * @return mixed false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    protected function _doContains($id)
    {
        if (false == $this->_memcached->getOption(Memcached::OPT_BINARY_PROTOCOL)) {
            $id = str_replace(' ', '_', $id);
        }
        return (bool) $this->_memcached->get($id);
    }

    /**
     * Save a cache record directly. This method is implemented by the cache
     * drivers and used in Doctrine_Cache_Driver::save()
     *
     * @param string $id        cache id
     * @param string $data      data to cache
     * @param int $lifeTime     if != 0, set a specific lifetime for this cache record (null => infinite lifeTime)
     * @return boolean true if no problem
     */
    protected function _doSave($id, $data, $lifeTime = 0)
    {
        if (false == $this->_memcached->getOption(Memcached::OPT_BINARY_PROTOCOL)) {
            $id = str_replace(' ', '_', $id);
        }

        return $this->_memcached->set($id, $data, $lifeTime);
    }

    /**
     * Remove a cache record directly. This method is implemented by the cache
     * drivers and used in Doctrine_Cache_Driver::delete()
     *
     * @param string $id cache id
     * @return boolean true if no problem
     */
    protected function _doDelete($id)
    {
        if (false == $this->_memcached->getOption(Memcached::OPT_BINARY_PROTOCOL)) {
            $id = str_replace(' ', '_', $id);
        }
        return $this->_memcached->delete($id);
    }

    /**
     * Fetch an array of all keys stored in cache
     *
     * @return array Returns the array of cache keys
     */
    protected function _getCacheKeys()
    {
        return $this->_memcached->getAllKeys();
    }
}