<?php

namespace MichelSteege\StructuredData;

use SilverStripe\Control\Director;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBField;

class StructuredData {

    private static $data = [];

    /**
     * Generate the breadcrumbs based on the sitetree
     * @param type $page
     * @param type $includeHome
     * @param type $homeTitle
     */
    public static function generateBreadcrumbsFromSiteTree($page, $includeHome = true, $homeTitle = 'Home') {
        $breadcrumbs = [];
        $startingPage = $page;
        
        while ($page) {
            $breadcrumbs[] = [
                'title' => $page->Title,
                'link' => $page->AbsoluteLink()
            ];
            $page = $page->ParentID ? $page->Parent() : false;
        }
        
        if ($includeHome && $startingPage->URLSegment != 'home') {
            $breadcrumbs[] = [
                'title' => $homeTitle,
                'link' => Director::absoluteBaseURL()
            ];
        }
        
        self::setBreadcrumbs(array_reverse($breadcrumbs));
    }

    /**
     * Set the breadcrumbs
     * Example array:
     * [
     *  [
     *      'title' => 'Home',
     *      'link' => 'https://example.com'
     *  ],
     *  [
     *      'title' => 'Blog',
     *      'link' => 'https://example.com/blog'
     *  ],
     *  [
     *      'title' => 'Blog item',
     *      'link' => 'https://example.com/blog/item'
     *  ]
     * ]
     * @param array $breadcrumbs
     */
    public static function setBreadcrumbs($breadcrumbs) {
        $structuredBreadcrumbs = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => []
        ];
        $count = 1;
        foreach ($breadcrumbs as $breadcrumbItem) {
            $structuredBreadcrumbs['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $count,
                'item' => [
                    '@id' => $breadcrumbItem['link'],
                    'name' => $breadcrumbItem['title']
                ]
            ];
            $count++;
        }
        self::$data[] = $structuredBreadcrumbs;
    }
    
    /**
     * Set information about the organization, it is recommended to only add it on the homepage and/or contact page
     * @param varchar $name the organization name
     * @param varchar $url the url of the website
     * @param bool|varchar $logoURL the url of the organization logo
     * @param bool|array $socialMedia set false for no social media or an array of social media url's
     */
    public static function setOrganizationData($name, $url, $logoURL = false, $socialMedia = false) {
        $organizationData = [
            '@type' => 'Organization',
            'name' => $name,
            'url' => $url
        ];
        if($logoURL){
            $organizationData['logo'] = $logoURL;
        }
        if($socialMedia && count($socialMedia) > 0){
            $organizationData['sameAs'] = $socialMedia;
        }
        self::$data[] = $organizationData;        
    }
    
    public static function setAggregateRating($rating, $numReviews, $type = 'LocalBusiness', $best = 10) {
        $ratingData = [
            '@type' => $type,
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $rating,
                'reviewCount' => $numReviews,
                'bestRating' => $best
            ]
        ];
        
        self::$data[] = $ratingData;      
    }

    /**
     * Render the structured data to string including the script tag
     * @return string
     */
    public static function render() {
        if (count(self::$data) == 0) {
            return '';
        }
        $json = [
            '@context' => 'https://schema.org',
            '@graph' => self::$data
        ];
        return DBField::create_field(DBHTMLText::class, '<script type="application/ld+json">' . json_encode($json) . '</script>');
    }

}
