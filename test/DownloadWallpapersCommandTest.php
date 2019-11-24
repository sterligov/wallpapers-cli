<?php


namespace App\Test;


use App\Command\DownloadWallpapersCommand;
use App\Wallpapers\WallpapersDownloaderFactoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Validator\Validation;

class DownloadWallpapersCommandTest extends TestCase
{
    private $command;

    const DOWNLOADED_FILES = 3;

    protected function setUp(): void
    {
        $factoryMock = $this
            ->getMockBuilder(WallpapersDownloaderFactoryInterface::class)
            ->disableOriginalConstructor()
            ->disableAutoload()
            ->setMethods(['createDownloader', 'download'])
            ->getMock();

        $factoryMock->expects($this->any())
            ->method('createDownloader')
            ->will($this->returnSelf());

        $factoryMock->expects($this->any())
            ->method('download')
            ->willReturn(self::DOWNLOADED_FILES);

        $command = new DownloadWallpapersCommand();
        $command->setWallpapersDownloaderFactory($factoryMock);
        $command->setValidator(Validation::createValidator());

        $app = new Application();
        $app->add($command);
        $command = $app->find('wallpapers:download');
        $this->command = new CommandTester($command);
    }

    public function badOptions()
    {
        return [
            [
                ['-m' => 'Bad month'],
                'Bad month - This value is not a valid datetime.',
            ],
            [
                ['-y' => 'Bad year'],
                'Bad year - This value is not a valid datetime.',
            ],
            [
                ['-c' => -1],
                'This value should be either positive or zero.',
            ]
        ];
    }

    /**
     * @param $options
     * @param $expectedError
     * @dataProvider badOptions
     */
    public function testBadOptions($options, $expectedError)
    {
        $this->command->execute($options);

        $this->assertStringContainsStringIgnoringCase($expectedError, $this->command->getDisplay());
    }

    public function testGoodOptions()
    {
        $this->command->execute([
            '-m' => 'December',
            '-y' => 2000,
            '-c' => 20
        ]);

        $this->assertStringContainsStringIgnoringCase(self::DOWNLOADED_FILES . ' wallpapers have been downloaded', $this->command->getDisplay());
    }
}