<?php

namespace Thodin\SitemapExtend\Models;

use Lovata\Shopaholic\Classes\Helper\CommonMenuType;
use Lovata\Shopaholic\Classes\Item\ProductItem;

/**
 * Created by PhpStorm.
 * User: Thodin
 * Date: 01.02.2021
 * Time: 1:00
 */

class ProductMenuType extends CommonMenuType
{
    const MENU_TYPE = 'all-shop-products';

    /**
     * Handler for the pages.menuitem.resolveItem event.
     * @param \RainLab\Pages\Classes\MenuItem $obMenuItem
     * @param string                          $sURL
     * @return array|mixed
     */
    public function resolveMenuItem($obMenuItem, $sURL)
    {

        $arResult = [];
        if (empty($obMenuItem->reference)) {
            return $arResult;
        }

        $obProductItem = ProductItem::make($obMenuItem->reference);
        if ($obProductItem->isEmpty()) {
            return $arResult;
        }

        $arResult = $this->getCategoryMenuData($obProductItem, $obMenuItem->cmsPage, $sURL);
        if (!$obMenuItem->nesting || $obProductItem->children->isEmpty()) {
            return $arResult;
        }

        $arResult['items'] = $this->getChildrenCategoryList($obProductItem, $obMenuItem->cmsPage, $sURL);

        return $arResult;
    }

    /**
     * Get default array for menu type
     * @return array|null
     */
    protected function getDefaultMenuTypeInfo()
    {
        $arResult = [
            'references'   => $this->listSubCategoryOptions(),
            'nesting'      => true,
            'dynamicItems' => true,
        ];

        return $arResult;
    }
}
