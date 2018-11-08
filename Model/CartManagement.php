<?php

namespace Monext\Payline\Model;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
use Monext\Payline\Model\OrderIncrementIdTokenFactory as OrderIncrementIdTokenFactory;

class CartManagement
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var OrderIncrementIdTokenFactory
     */
    protected $orderIncrementIdTokenFactory;

    /**
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        CartManagementInterface $cartManagement,
        OrderIncrementIdTokenFactory $orderIncrementIdTokenFactory,
        QuoteFactory $quoteFactory,
        CheckoutCart $checkoutCart,
        ProductCollectionFactory $productCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory
    )
    {
        $this->cartRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
        $this->quoteFactory = $quoteFactory;
        $this->orderIncrementIdTokenFactory = $orderIncrementIdTokenFactory;
        $this->checkoutCart = $checkoutCart;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    public function handleReserveCartOrderId($cartId, $forceReserve = false)
    {
        $cart = $this->cartRepository->getActive($cartId);

        if($forceReserve) {
            $cart->setReservedOrderId(null);
        }

        if(!$cart->getReservedOrderId()) {
            $cart->reserveOrderId();
            $this->cartRepository->save($cart);
        }

        return $this;
    }

    public function placeOrderByToken($token)
    {
        $quote = $this->getCartByToken($token);
        $this->cartManagement->placeOrder($quote->getId());
        return $this;
    }

    public function restoreCartFromOrder(Order $order)
    {
        foreach($order->getItemsCollection() as $orderItem) {
            $this->checkoutCart->addOrderItem($orderItem);
        }

        // TODO Handle couponCode

        $this->checkoutCart->save();
        return $this;
    }

    public function getCartByToken($token)
    {
        $orderIncrementId = $this->orderIncrementIdTokenFactory->create()->getOrderIncrementIdByToken($token);
        // TODO Use QuoteRepository instead of quote::load
        return $this->quoteFactory->create()->load($orderIncrementId, 'reserved_order_id');
    }

    public function getProductCollectionFromCart($cartId)
    {
        $cart = $this->cartRepository->getActive($cartId);

        $productIds = [];
        $categoryIds = [];
        $productCollection = $this->productCollectionFactory->create();
        $categoryCollection = $this->categoryCollectionFactory->create();

        foreach ($cart->getItems() as $item) {
            $productIds[] = $item->getProductId();
        }

        $productCollection
            ->addAttributeToFilter('entity_id', ['in' => $productIds])
            ->addAttributeToSelect('*')
            ->addCategoryIds();

        foreach ($productCollection as $product) {
            $categoryIds = array_merge($categoryIds, $product->getCategoryIds());
        }

        $categoryCollection
            ->addAttributeToFilter('entity_id', ['in' => $categoryIds])
            ->addAttributeToSelect(['name', 'payline_category_mapping', 'level']);

        foreach ($productCollection as $product) {
            $categoryCandidate = null;

            foreach ($product->getCategoryIds() as $categoryId) {
                $tmpCategory = $categoryCollection->getItemById($categoryId);

                if (!$tmpCategory) {
                    continue;
                }

                if (!$categoryCandidate || $tmpCategory->getLevel() > $categoryCandidate->getLevel()) {
                    $categoryCandidate = $tmpCategory;
                }
            }

            $product->setPaylineCategoryMapping(
                $categoryCandidate->getPaylineCategoryMapping() ? $categoryCandidate->getPaylineCategoryMapping() : $categoryCandidate->getName()
            );
        }

        return $productCollection;
    }
}

