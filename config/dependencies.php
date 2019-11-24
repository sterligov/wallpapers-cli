<?php

use \Symfony\Component\Validator\Validator\ValidatorInterface;
use \Symfony\Component\Validator\Validator\RecursiveValidator;
use \App\Wallpapers\WallpapersDownloaderInterface;
use \App\Wallpapers\SmashingMagazine;
use \App\Wallpapers\WallpapersDownloaderFactoryInterface;
use \App\Command\DownloadWallpapersCommand;
use \Symfony\Component\Validator\Validation;
use \App\Wallpapers\WallpapersDownloaderFactory;

return [
    WallpapersDownloaderInterface::class => DI\autowire(SmashingMagazine::class),

    ValidatorInterface::class => DI\autowire(RecursiveValidator::class),

    WallpapersDownloaderFactoryInterface::class => DI\autowire(WallpapersDownloaderFactory::class),

    DownloadWallpapersCommand::class => \DI\Create()
        ->method('setValidator', Validation::createValidator())
        ->method('setWallpapersDownloaderFactory', DI\get(WallpapersDownloaderFactoryInterface::class))
];