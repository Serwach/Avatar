<?php

declare(strict_types=1);

namespace Monogo\Task\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Monogo\Task\Enum\ConfigEnum;

class ConfigService
{
    /** @var ScopeConfigInterface */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $storeId
     *
     * @return bool
     */
    public function isEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            ConfigEnum::XML_IS_ENABLED_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $storeId
     *
     * @return string
     */
    public function getAPIKey($storeId = null): string
    {
        return $this->scopeConfig->getValue(
            ConfigEnum::XML_API_KEY_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
