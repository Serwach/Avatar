<?php

declare(strict_types=1);

namespace Monogo\Task\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerExtensionInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Monogo\Task\Enum\ConfigEnum;
use Monogo\Task\Service\ConfigService;

class CustomerRepository
{
    /** @var CustomerExtensionInterfaceFactory */
    private CustomerExtensionInterfaceFactory $customerExtensionFactory;

    /** @var StoreManagerInterface */
    private StoreManagerInterface $storeManager;

    /** @var ConfigService */
    private ConfigService $configService;

    /**
     * @param CustomerExtensionInterfaceFactory $customerExtensionFactory
     * @param ConfigService $configService
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CustomerExtensionInterfaceFactory $customerExtensionFactory,
        ConfigService $configService,
        StoreManagerInterface $storeManager
    ) {
        $this->customerExtensionFactory = $customerExtensionFactory;
        $this->storeManager = $storeManager;
        $this->configService = $configService;
    }

    /**
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $resultCustomer
     *
     * @return CustomerInterface
     */
    public function afterGetById(
        CustomerRepositoryInterface $subject,
        CustomerInterface           $resultCustomer
    ) {
        return $this->getCustomerData($resultCustomer);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function getAvatarFromApi(string $name): string
    {
        return ConfigEnum::BASE_URL . '?api_key=' . $this->configService->getAPIKey() . '&name=' . $name;
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return CustomerInterface
     */
    private function getCustomerData(CustomerInterface $customer): CustomerInterface
    {
        $extensionAttributes = $customer->getExtensionAttributes();
        $customerExtension = $extensionAttributes ?: $this->customerExtensionFactory->create();
        $name = $customer->getFirstname() . ' ' . $customer->getLastname();
        $avatar = $this->getAvatarFromApi($name);

        if ($customer->getCustomAttribute('avatar')->getValue()) {
            $avatar = $customer->getCustomAttribute('avatar')->getValue();

            try {
                $avatar = $this->storeManager->getStore()->getBaseUrl(
                        UrlInterface::URL_TYPE_MEDIA
                    ) . 'customer' . $avatar;
            } catch (NoSuchEntityException $e) {
                return $customer;
            }
        }

        $customerExtension->setAvatar($avatar);
        $customer->setExtensionAttributes($customerExtension);

        return $customer;
    }
}
