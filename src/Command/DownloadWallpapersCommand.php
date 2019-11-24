<?php

namespace App\Command;


use App\Wallpapers\WallpapersDownloaderFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DownloadWallpapersCommand extends Command
{
    /**
     * @var WallpapersDownloaderFactoryInterface
     */
    private $wallpaperFactory;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var string
     */
    protected static $defaultName = 'wallpapers:download';

    /**
     * @param WallpapersDownloaderFactoryInterface $wallpaperFactory
     */
    public function setWallpapersDownloaderFactory(WallpapersDownloaderFactoryInterface $wallpaperFactory)
    {
        $this->wallpaperFactory = $wallpaperFactory;
    }

    /**
     * @param ValidatorInterface $validator
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    protected function configure()
    {
        $date = new \DateTime();
        $this
            ->setDescription('Download wallpapers from smashingmagazine.com')
            ->setHelp('Every month smashingmagazine.com public pack of wallpapers. This cli app can help you download this wallpapers')
            ->addOption('site', 's', InputOption::VALUE_REQUIRED, 'Wallpapers site', 'smashingmagazine')
            ->addOption('count', 'c', InputOption::VALUE_REQUIRED, 'Max number of wallpapers(0 - all)', 0)
            ->addOption('folder', 'f', InputOption::VALUE_REQUIRED, 'Download folder', './')
            ->addOption('month', 'm', InputOption::VALUE_REQUIRED, 'Month(December, January, etc.)', $date->format('F'))
            ->addOption('year', 'y', InputOption::VALUE_REQUIRED, 'Year(2018, 2019, etc.)', $date->format('Y'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOptions();
        $options['month'] = ucfirst(strtolower($options['month']));

        $errors = $this->validator->validate([
            'month' => $options['month'],
            'year' => $options['year'],
            'count' => $options['count']
        ], new Collection([
            'month' => new DateTime(['format' => 'F']),
            'year' => new DateTime(['format' => 'Y']),
            'count' => new PositiveOrZero()
        ]));

        if (count($errors) !== 0) {
            foreach ($errors as $error) {
                $output->write($error->getInvalidValue() . ' - ' . $error->getMessage()  . PHP_EOL);
            }
            return;
        }

        $output->write('Download start. This may take some time' . PHP_EOL);

        try {
            $downloader = $this->wallpaperFactory->createDownloader($options);
            $saved = $downloader->download($options['folder'], $options['count']);
            $output->write("$saved wallpapers have been downloaded" . PHP_EOL);
        } catch (\Throwable $e) {
            $output->write($e->getMessage() . PHP_EOL);
        }
    }
}