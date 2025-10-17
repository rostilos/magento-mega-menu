<?php

namespace Rostilos\MegaMenu\Helper;

use Magento\Framework\App\Cache as AppCache;
use Magento\Framework\App\Cache\State;
use Magento\Framework\App\Helper;
use Magento\Store\Model\StoreManagerInterface;

class Cache extends Helper\AbstractHelper
{
    const CACHE_TAG = 'UBMEGAMENU';

    const CACHE_ID = 'rsmegamenu';

    const CACHE_LIFETIME = 86400;

    protected $cache;
    protected $cacheState;
    protected $storeManager;
    private $storeId;

    public function __construct(
        Helper\Context $context,
        AppCache $cache,
        State $cacheState,
        StoreManagerInterface $storeManager
    ) {
        $this->cache = $cache;
        $this->cacheState = $cacheState;
        $this->storeManager = $storeManager;
        $this->storeId = $storeManager->getStore()->getId();

        parent::__construct($context);
    }

    public function getId($method, $vars = array())
    {
        return base64_encode($this->storeId . self::CACHE_ID . $method . implode('', $vars));
    }

    public function load($cacheId)
    {
        if ($this->cacheState->isEnabled(self::CACHE_ID)) {
            return $this->cache->load($cacheId);
        }

        return false;
    }

    public function save($data, $cacheId, $cacheLifetime = self::CACHE_LIFETIME)
    {
        if ($this->cacheState->isEnabled(self::CACHE_ID)) {
            $this->cache->save($data, $cacheId, array(self::CACHE_TAG), $cacheLifetime);
            return true;
        }

        return false;
    }

}
