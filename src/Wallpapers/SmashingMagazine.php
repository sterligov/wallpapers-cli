<?php


namespace App\Wallpapers;


use App\Exception\PostNotFoundException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DomCrawler\Crawler;
use function GuzzleHttp\Promise\settle;

class SmashingMagazine implements WallpapersDownloaderInterface
{
    const URL = 'https://www.smashingmagazine.com';

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * @var string
     */
    private $date;

    /**
     * SmashingMagazine constructor.
     * @param Client $httpClient
     * @param Crawler $crawler
     */
    public function __construct(Client $httpClient, Crawler $crawler)
    {
        $this->httpClient = $httpClient;
        $this->crawler = $crawler;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date->format('/Y/m');
    }

    /**
     * @param string $folder
     * @param int $maxWallpapersNumber
     * @return int
     * @throws PostNotFoundException
     */
    public function download(string $folder, int $maxWallpapersNumber): int
    {
        $postURL = $this->findPostURLByDate();
        if (!$postURL) {
            throw new PostNotFoundException('Cannot find post with specified date');
        }

        $images = $this->findImageURLs($postURL);
        if ($maxWallpapersNumber) {
            $images = array_slice($images, 0, $maxWallpapersNumber);
        }

        $promises = [];
        foreach ($images as $imageName => $url) {
            $promises[$imageName] = $this->httpClient->getAsync($url);
        }

        $responses = settle($promises)->wait();
        $nSuccessfullySaved = 0;

        foreach ($responses as $imageName => $response) {
            if ($response['state'] === 'fulfilled' && $response['value']->getBody()->isReadable()) {
                $imageName = trim($folder, '/') . '/' . $imageName;
                if (file_put_contents($imageName, $response['value']->getBody()->getContents()) !== false) {
                    ++$nSuccessfullySaved;
                }
            }
        }

        return $nSuccessfullySaved;
    }

    /**
     * @return string
     */
    private function findPostURLByDate(): string
    {
        $wallpapersURL = self::URL . '/category/wallpapers';
        $response = $this->httpClient->get($wallpapersURL);
        $body = $response->getBody()->getContents();

        $this->crawler->clear();
        $this->crawler->addHtmlContent($body);
        $node = $this->crawler->filterXPath(
            "//*[@class='tilted-featured-article__title']/a[starts-with(@href, '$this->date')]/@href"
        );

        if ($node->count()) {
            return $node->text();
        }

        $pageNum = 1;
        while (true) {
            try {
                $url = $pageNum == 1 ? $wallpapersURL : $wallpapersURL . "/page/$pageNum";
                $response = $this->httpClient->get($url);
                ++$pageNum;
            } catch (RequestException $e) {
                return '';
            }

            $body = $response->getBody()->getContents();

            $this->crawler->clear();
            $this->crawler->addHtmlContent($body);
            $node = $this->crawler->filterXPath(
                "//*[@class='read-more-link'][starts-with(@href, '$this->date')]/@href"
            );

            if ($node->count()) {
                return $node->text();
            }
        }
    }

    /**
     * @param string $postURL
     * @return  array
     */
    private function findImageURLs(string $postURL): array
    {
        try {
            $response = $this->httpClient->get(self::URL . $postURL);
        } catch (RequestException $e) {
            return [];
        }

        $body = $response->getBody()->getContents();
        $this->crawler->clear();
        $this->crawler->add($body);

        // get largest image
        $nodes = $this->crawler->filterXPath('//main//ul//li[last()]//a[last()]');

        $urls = [];
        foreach ($nodes as $node) {
            if (preg_match('/\d+x\d+/',$node->nodeValue)) {
                $urls[$node->getAttribute('title')] = $node->getAttribute('href');
            }
        }

        return $urls;
    }
}