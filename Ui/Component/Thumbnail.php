<?php

declare (strict_types=1);

namespace Monogo\Task\Ui\Component;

use Magento\Catalog\Helper\Image;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Monogo\Task\Enum\ConfigEnum;
use Monogo\Task\Service\ConfigService;

class Thumbnail extends Column
{
    /** @var Image */
    private Image $image;

    /** @var StoreManagerInterface */
    private StoreManagerInterface $storeManager;

    /** @var ConfigService */
    private ConfigService $configService;

    /**
     * @param Image $image
     * @param StoreManagerInterface $storeManager
     * @param ConfigService $configService
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Image $image,
        StoreManagerInterface $storeManager,
        ConfigService $configService,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->storeManager = $storeManager;
        $this->image = $image;
        $this->configService = $configService;
    }

    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');

            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['avatar'] === null) {
                    $item = $this->getAvatarFromApi($fieldName, $item);
                } else {
                    $item = $this->getAvatar($fieldName, $item);
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param string $fieldName
     * @param array $item
     *
     * @return array
     */
    private function getAvatar(string $fieldName, array $item): array
    {
        $url = $this->image->getDefaultPlaceholderUrl('small_image');

        if (isset($item[$fieldName]) && $item[$fieldName] != '') {
            try {
                $url = $this->storeManager->getStore()->getBaseUrl(
                        UrlInterface::URL_TYPE_MEDIA
                    ) . 'customer' . $item[$fieldName];
            } catch (NoSuchEntityException $e) {
                return $item;
            }
        }

        $item[$fieldName . '_src'] = $url;
        $item[$fieldName . '_orig_src'] = $url;

        return $item;
    }

    /**
     * @param string $fieldName
     * @param array $item
     *
     * @return array
     */
    private function getAvatarFromApi(string $fieldName, array $item): array
    {
        $url = ConfigEnum::BASE_URL . '?api_key=' . $this->configService->getAPIKey() . '&name=' . $item['name'];
        $item[$fieldName . '_src'] = $url;
        $item[$fieldName . '_orig_src'] = $url;

        return $item;
    }
}
