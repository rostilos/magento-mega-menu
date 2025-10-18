<?php
namespace Rostilos\Base\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;

class Data extends AbstractHelper
{
    const XML_PATH_SECURE_IN_FRONTEND = 'web/secure/use_in_frontend';

    const XML_PATH_SECURE_BASE_URL = 'web/secure/base_url';

    const XML_PATH_UNSECURE_BASE_URL = 'web/unsecure/base_url';

    const XML_PATH_USE_REWRITES = 'web/seo/use_rewrites';

    protected Serializer $serializer;

    protected StoreManagerInterface $storeManager;

    protected ReinitableConfigInterface $appConfig;

    /** @var CustomerSessionFactory */
    protected $customerSessionFactory;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Serializer $serializer,
        ReinitableConfigInterface $appConfig,
        CustomerSessionFactory $customerSessionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->appConfig = $appConfig;
        $this->customerSessionFactory = $customerSessionFactory;

        parent::__construct($context);
    }

    public function getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function getStoreManager()
    {
        return $this->storeManager;
    }

    public function getSerializer()
    {
        return $this->serializer;
    }

    public function getConfigValue($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getConfigValueByKey($key = null, $sections = [], $initData = [])
    {
        /** @var \Magento\Framework\App\ScopeResolverInterface $scopeResolver */
        $scopeResolver = $this->getObjectManager()->get('\Magento\Framework\App\ScopeResolverInterface');
        $scopeCode = $scopeResolver->getScope()->getCode();
        $currentStoreCode = $this->storeManager->getStore()->getCode();
        $currentWebsiteCode = $this->storeManager->getWebsite()->getCode();
        if ($scopeCode == $currentStoreCode) {
            $scope = ScopeInterface::SCOPE_STORES;
        } elseif ($scopeCode == $currentWebsiteCode) {
            $scope = ScopeInterface::SCOPE_WEBSITES;
        } else {
            $scope = 'default';
            //$scopeId = 0;
            $scopeCode = '';
        }

        $value = null;
        if (isset($initData[$key])) {
            $value = $initData[$key];
        } else {
            foreach ($sections as $section) {
                $groups = $this->appConfig->getValue($section, $scope, $scopeCode);
                if ($groups) {
                    foreach ($groups as $configs) {
                        if (isset($configs[$key])) {
                            $value = $configs[$key];
                            break;
                        }
                    }
                }
                if ($value) {
                    break;
                }
            }
        }

        return $value;
    }

    public function getBaseUrl()
    {
        $isSecure = (int) $this->scopeConfig->getValue(
            self::XML_PATH_SECURE_IN_FRONTEND,
            ScopeInterface::SCOPE_STORE
        );
        $urlSecure = $this->scopeConfig->getValue(
            self::XML_PATH_SECURE_BASE_URL,
            ScopeInterface::SCOPE_STORE
        );
        $urlUnsecure = $this->scopeConfig->getValue(
            self::XML_PATH_UNSECURE_BASE_URL,
            ScopeInterface::SCOPE_STORE
        );
        $isUseRewrites = $this->scopeConfig->getValue(
            self::XML_PATH_USE_REWRITES,
            ScopeInterface::SCOPE_STORE
        );
        $url = ($isSecure) ?  $urlSecure : $urlUnsecure;

        return ($isUseRewrites) ? $url : ($url.'index.php/');
    }

    public function getRequest()
    {
        return $this->getObjectManager()->get('\Magento\Backend\App\Action\Context')->getRequest();
    }
}
