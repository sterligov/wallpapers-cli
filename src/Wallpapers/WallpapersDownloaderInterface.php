<?php


namespace App\Wallpapers;


interface WallpapersDownloaderInterface
{
    /**
     * @param string $folder
     * @param int $maxWallpapersNumber
     * @return int
     */
    public function download(string $folder, int $maxWallpapersNumber): int;
}