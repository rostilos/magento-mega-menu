<?php
namespace Rostilos\Base\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;

/**
 * Data helper
 */
class Data extends AbstractHelper
{
    const XML_PATH_SECURE_IN_FRONTEND = 'web/secure/use_in_frontend';

    const XML_PATH_SECURE_BASE_URL = 'web/secure/base_url';

    const XML_PATH_UNSECURE_BASE_URL = 'web/unsecure/base_url';

    const XML_PATH_USE_REWRITES = 'web/seo/use_rewrites';

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Application config
     *
     * @var ReinitableConfigInterface
     */
    protected $appConfig;

    /** @var CustomerSessionFactory */
    protected $customerSessionFactory;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Serializer $serializer
     * @param ReinitableConfigInterface $appConfig
     * @param CustomerSessionFactory $customerSessionFactory
     */
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

    /**
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @return Serializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Get config value by config path
     *
     * @param  string $path
     * @param  int $storeId
     * @return mixed
     */
    public function getConfigValue($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $key
     * @param array $sections
     * @param array $initData
     * @return mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
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

    /**
     * Get sub string from string with limit length
     *
     * @param $text
     * @param $maxLength
     * @param string $end
     * @return string
     */
    public static function subStrWords($text = '', $maxLength = 0, $end = '...')
    {
        if ($text && strlen($text) > $maxLength) {
            $words = explode(" ", $text);
            $output = '';
            $i = 0;
            while (1) {
                $length = (strlen($output) + strlen($words[$i]));
                if ($length > $maxLength) {
                    break;
                } else {
                    $output = $output . " " . $words[$i];
                    ++$i;
                };
            };
        } else {
            $output = $text;
        }

        return ($output) ? $output . $end : $output;
    }

    public function getAjaxCompareOptions()
    {
        $options = [
            'ajaxCompareUrl' => $this->_getUrl('catalog/product_compare/add/')
        ];

        return $this->serializer->serialize($options);
    }

    public function getAjaxWishlistOptions()
    {
        $customerId = $this->getCustomerId();

        $options = [
            'ajaxWishlistUrl' => $this->_getUrl('wishlist/index/add/'),
            'loginUrl' => $this->_getUrl('customer/account/login'),
            'customerId' => $customerId
        ];

        return $this->serializer->serialize($options);
    }

    public function getAjaxCartOptions()
    {
        $options = [
            'ajaxCartUrl' => $this->_getUrl('checkout/cart/add/')
        ];

        return $this->serializer->serialize($options);
    }

    public function getCustomerId()
    {
        return $this->customerSessionFactory->create()->getCustomer()->getId();
    }

    /**
     * @return mixed
     */
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

    /**
     * Get current request object
     * @return mixed
     */
    public function getRequest()
    {
        return $this->getObjectManager()->get('\Magento\Backend\App\Action\Context')->getRequest();
    }

    /**
     * Get current url
     */
    public function getCurrentUrl()
    {
        /** @var \Magento\Framework\UrlInterface $urlInterface */
        $urlInterface = $this->getObjectManager()->get('Magento\Framework\UrlInterface');
        return $urlInterface->getCurrentUrl();
    }

    /**
     * Get current remote IP function
     */
    public function getCurrentRemoteIp()
    {
        /** @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $client */
        $client = $this->getObjectManager()->get('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress');
        return $client->getRemoteAddress();
    }

    /**
     * Check if module is enabled
     */
    public function isEnabled($moduleName)
    {
        return $this->_moduleManager->isEnabled($moduleName);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * @return string
     */
    public function getDevice()
    {
        $queryContent = $this->getRequest()->getContent();
        $devices = ['mobile', 'tablet', 'desktop'];

        foreach ($devices as $device) {
            if (str_contains($queryContent, $device)) {
                $result = $device;
            }
        }

        return $result;
    }
}
