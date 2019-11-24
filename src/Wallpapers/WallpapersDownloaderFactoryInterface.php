<?php


namespace App\Wallpapers;


interface WallpapersDownloaderFactoryInterface
{
    /**
     * @param array $options
     * @return WallpapersDownloaderInterface
     */
    public function createDownloader(array $options): WallpapersDownloaderInterface;
}