<?php


namespace App\Test;


use App\Container;
use App\Exception\WallpapersFactoryException;
use App\Wallpapers\SmashingMagazine;
use App\Wallpapers\WallpapersDownloaderFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class WallpapersDownloaderFactoryTest extends TestCase
{
    private static $factory;

    public static function setUpBeforeClass(): void
    {
        $containerBuilder = new \DI\ContainerBuilder();
        $containerBuilder->addDefinitions(__DIR__ . '/../config/dependencies.php');
        self::$factory = new WallpapersDownloaderFactory($containerBuilder->build());
    }

    public function testCreateDownloader()
    {
        $downloader = self::$factory->createDownloader([
            'site' => 'smashingmagazine',
            'year' => '2000',
            'month' => 'December'
        ]);

        $this->assertEquals(SmashingMagazine::class, get_class($downloader));
    }

    public function testCreateDownloaderException()
    {
        $this->expectException(WallpapersFactoryException::class);
        self::$factory->createDownloader(['site' => 'bad_site']);
    }
}