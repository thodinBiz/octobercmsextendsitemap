<?php namespace Thodin\SitemapExtend;

use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;
use Event;
use October\Rain\Support\Collection;
use System\Classes\PluginBase;
use Thodin\SitemapExtend\Models\Product;

/**
 * Class Plugin
 * @package Thodin\SitemapExtend
 */
class Plugin extends PluginBase
{

    /**
     *
     */
    const MENU_TYPE = 'all-shop-products';

    /**
     * @var array
     */
    public $require = ['Lovata.Shopaholic'];

    /**
     * Plugin boot method
     */
    public function boot()
    {
        $this->bootMenuItem();
    }

    /**
     *
     */
    private function bootMenuItem()
    {
        Event::listen('pages.menuitem.listTypes', function () {
            return [
                self::MENU_TYPE => 'thodin.sitemapextend::lang.menu.products',
            ];
        });

        Event::listen('pages.menuitem.getTypeInfo', function ($type) {
            if ($type == self::MENU_TYPE) {
                $obTheme = Theme::getActiveTheme();
                $obPageList = CmsPage::listInTheme($obTheme, true);

                return [
                    'dynamicItems' => true,
                    'cmsPages'     => $this->filterPageList($obPageList),
                ];
            }
        });

        Event::listen('pages.menuitem.resolveItem', function ($sType, $obItem, $sUrl, $obTheme) {
            if ($sType == self::MENU_TYPE) {
                return Product::resolveMenuItem($obItem, $sUrl, $obTheme);
            }
        });
    }

    /**
     * Filter page list, add pages with CategoryPage component to result
     *
     * @param Collection $obPageList
     *
     * @return array
     */
    protected function filterPageList($obPageList)
    {
        $arCmsPageList = [];
        if (empty($obPageList) || $obPageList->isEmpty()) {
            return $arCmsPageList;
        }

        /** @var CmsPage $obPage */
        foreach ($obPageList as $obPage) {
            if (!$obPage->hasComponent('ProductPage')) {
                continue;
            }

            /*
             * Component must use a category filter with a routing parameter
             * eg: categoryFilter = "{{ :somevalue }}"
             */
            $arPropertyList = $obPage->getComponentProperties('ProductPage');
            if (!isset($arPropertyList['slug']) || !preg_match('/{{\s*:/', $arPropertyList['slug'])) {
                continue;
            }
            $arCmsPageList[] = $obPage;
        }

        return $arCmsPageList;
    }
}
