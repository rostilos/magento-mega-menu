<?php
namespace Rostilos\MegaMenu\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class GroupActions extends Column
{
    /** Url path */
    const UB_MEGA_MENU_URL_PATH_EDIT = 'rsmegamenu/group/edit';
    const UB_MEGA_MENU_URL_PATH_DELETE = 'rsmegamenu/group/delete';
    const UB_MEGA_MENU_URL_PATH_CLONE = 'rsmegamenu/group/duplicate';
    const UB_MEGA_MENU_URL_PATH_LIST_ITEMS = 'rsmegamenu/item/index';

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::UB_MEGA_MENU_URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->editUrl = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['group_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->editUrl, ['group_id' => $item['group_id']]),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::UB_MEGA_MENU_URL_PATH_DELETE,
                            ['group_id' => $item['group_id']]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Confirm'),
                            'message' => __(
                                'Are you sure to delete the selected item?'
                                .'  All it\'s child items will be deleted too.'
                            )
                        ]
                    ];
                    $item[$name]['listitem'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::UB_MEGA_MENU_URL_PATH_LIST_ITEMS,
                            ['group_id' => $item['group_id']]
                        ),
                        'label' => __('Manage Menu Items')
                    ];
                    $item[$name]['duplicate'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::UB_MEGA_MENU_URL_PATH_CLONE,
                            ['group_id' => $item['group_id']]
                        ),
                        'label' => __('Make Duplicate')
                    ];
                }
            }
        }

        return $dataSource;
    }
}
