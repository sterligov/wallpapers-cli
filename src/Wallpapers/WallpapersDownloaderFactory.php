<?php


namespace App\Wallpapers;


use App\Exception\WallpapersFactoryException;
use Psr\Container\ContainerInterface;

class WallpapersDownloaderFactory implements WallpapersDownloaderFactoryInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * WallpapersDownloaderFactory constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $options
     * @return WallpapersDownloaderInterface
     * @throws WallpapersFactoryException
     */
    public function createDownloader(array $options): WallpapersDownloaderInterface
    {
        if ($options['site'] == 'smashingmagazine') {
            $downloader = $this->container->get(SmashingMagazine::class);
            $downloader->setDate(new \DateTime("{$options['year']}-{$options['month']}-01"));

            return $downloader;
        }

        throw new WallpapersFactoryException('Cannot create instance by given options');
    }
}