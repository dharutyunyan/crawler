<?php

namespace App\Services;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class AmazonService
{

    private $client;

    public function __construct()
    {
        $this->client = new Client([
                'timeout'   => 10,
                'verify'    => false
            ]);
    }

    public function getRecommendation($keyword)
    {
        try {
            $content = $this->getAmazonRecommendationPageContent($keyword);
            $crawler = new Crawler( $content, "https://www.amazon.com" );

            $_this = $this;
            $data = $crawler->filter('div.s-shopping-adviser')
                            ->each(function (Crawler $node, $i) use($_this) {
                                return $_this->getNodeContent($node);
                            }
                        );
            if(!empty($data)){
                if(isset($data[0]['author']) && $data[0]['author']){
                    return $data[0];
                }else if(isset($data[1]['author']) && $data[1]['author']){
                    return $data[1];
                }
                return false
            }else{
                return false
            }


        } catch ( Exception $e ) {
                echo $e->getMessage();
        }
    }

    public function getRecommendationsForSet($keywordSet)
    {
        $data = [];
        foreach($keywordSet as $keyword){
            $data[] = $this->getRecommendations($keyword);
        }
        return $data;
    }

    /**
     * Check is content available
     */
    private function hasContent($node)
    {
        return $node->count() > 0 ? true : false;
    }
    /**
     * Get node values
     * @filter function required the identifires, which we want to filter from the content.
     */

    private function getNodeContent($node)
    {
        $array = [
            'author' => $this->hasContent($node->filter('div.s-shopping-adviser-heading')->filter('.a-link-normal')) != false ? $node->filter('div.s-shopping-adviser-heading')->filter('.a-link-normal')->text() : '',
            'article' => $this->hasContent($node->filter('.a-carousel-card h5')) != false ? $node->filter('.a-carousel-card h5')->text() : '',
            'published_at' => $this->hasContent($node->filter('span.a-color-secondary')) != false ? $node->filter('span.a-color-secondary')->text() : '',
            'article_url' => $this->hasContent($node->selectLink('Read full article')) != false ? $node->selectLink('Read full article')->link()->getUri() : ''
        ];
        return $array;
    }

    private function getAmazonRecommendationPageContent($keyword)
    {
        $url = "https://www.amazon.com/s?k=" . urlencode($keyword) . "&ref=nb_sb_noss_2";
        $response = $this->client->get($url); // URL, where you want to fetch the content
        // get content from Amazon
        return $response->getBody()->getContents();
    }
}
?>
