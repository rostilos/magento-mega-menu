<?php
/**
 * Copyright © 2016 Rostilos.com All rights reserved.
 */

namespace Rostilos\Base\Plugin\Controller\Wishlist\Index;

use Magento\Framework\App\Action;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Rostilos\Base\Helper\Data as BaseHelper;

/**
 * Ajax add product to wishlist function
 */
class Add extends \Magento\Wishlist\Controller\Index\Add
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirect;

    /** @var BaseHelper */
    protected $baseHelper;

    /**
     * Add constructor.
     * @param Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     * @param Validator $formKeyValidator
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param BaseHelper $baseHelper
     */
    public function __construct(
        Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository,
        Validator $formKeyValidator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        BaseHelper $baseHelper
    ) {
        parent::__construct($context, $customerSession, $wishlistProvider, $productRepository, $formKeyValidator);

        $this->storeManager = $storeManager;
        $this->resultRedirect = $redirectFactory;
        $this->baseHelper = $baseHelper;
    }

    /**
     * @param $subject
     * @param \Closure $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function aroundExecute($subject, \Closure $proceed)
    {
        //check if is not enabled
        $isEnabled = $this->baseHelper->getConfigValue('rostilos_general/general/enable_ajax_wishlist');
        if (!$isEnabled) {
            return $proceed();
        }

        $result = [];
        $params = $subject->getRequest()->getParams();
        $product = $this->_initProduct($subject);

        if (isset($params['isAjaxWishlist']) && $params['isAjaxWishlist']) {
            $proceed();
            $result['success'] = true;
            $result['message'] = __("%1 has been added to your Wish List", $product->getName());
            $subject->getResponse()->representJson($this->baseHelper->getSerializer()->serialize($result));
        } else {
            $proceed();
            return $this->resultRedirect->create()->setPath('*');
        }
    }

    /**
     * @param $subject
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     * @throws NoSuchEntityException
     */
    protected function _initProduct($subject)
    {
        $productId = (int)$subject->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->storeManager->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }
}
