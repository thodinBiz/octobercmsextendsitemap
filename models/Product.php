<?php

namespace Thodin\SitemapExtend\Models;

/**
 * Created by PhpStorm.
 * User: Thodin
 * Date: 01.02.2021
 * Time: 0:29
 */


use Cms\Classes\Page;
use Cms\Classes\Theme;
use Lovata\Shopaholic\Classes\Collection\CategoryCollection;
use Lovata\Shopaholic\Classes\Collection\ProductCollection;
use Lovata\Shopaholic\Classes\Item\ProductItem;
use Lovata\Shopaholic\Models\Product as ShopaholicProduct;

class Product extends ShopaholicProduct
{
    /**
     * @param ShopaholicProduct $obMenuItem
     * @param string            $sURL
     * @param Theme             $obTheme
     *
     * @return array
     */
    public static function resolveMenuItem($obMenuItem, $sURL, $obTheme)
    {
        $arResult = [
            'items' => [],
        ];

        //Get category list with sorted by 'nest_left'
        $obProductList = ProductCollection::make()->active();
        if ($obProductList->isEmpty())
        {
            return $arResult;
        }

        /** @var ProductItem $obProductItem */
        foreach ($obProductList as $obProduct)
        {
            $obProductItem = ProductItem::make($obProduct->id, $obProduct);

            $arResult['items'][] = [
                'title'    => $obProductItem->name,
                'url'      => $obProductItem->getPageUrl($obMenuItem->cmsPage),
                'mtime'    => $obProductItem->updated_at,
                'isActive' => $obMenuItem->cmsPage === $sURL,
            ];
        }

        return $arResult;


    }

    /**
     * @param        $obMenuItem
     * @param string $sURL
     * @param        $obTheme
     *
     * @return array
     */
    private function sStartFunction($obMenuItem, $sURL, $obTheme)
    {
        $pageName = 'your-page-file';
        $cmsPage = Page::loadCached($obTheme, $pageName);

        $items = self::orderBy('sort_order', 'ASC')->get()->map(function (self $item) use ($cmsPage, $sURL, $pageName) {
            $pageUrl = $cmsPage->url($pageName, ['slug' => $item->slug]);

            return [
                'title'    => $item->name,
                'url'      => $pageUrl,
                'mtime'    => $item->updated_at,
                'isActive' => $pageUrl === $sURL,
            ];
        })->toArray();

        return [
            'items' => $items,
        ];
    }

    /**
     * @param ProductItem $obProductItem
     * @param string      $cmsPage
     * @param string      $sURL
     *
     * @return array
     */
    protected static function getProductMenuData(ProductItem $obProductItem, string $cmsPage, string $sURL)
    {
        if (empty($obProductItem))
        {
            return [];
        }

        $arMenuItem = [
            'title'   => $obProductItem->name,
            'url'     => $obProductItem->getPageUrl($cmsPage),
            'mtime'   => $obProductItem->updated_at,
            'viewBag' => ['object' => $obProductItem],
        ];

        $arMenuItem['isActive'] = $arMenuItem['url'] == $sURL;

        return $arMenuItem;
    }
}
