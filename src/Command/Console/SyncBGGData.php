<?php

namespace App\Command\Console;

use App\Command\GetBGGData;
use App\Command\GetBGGDataHandler;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncBGGData extends Command
{
    /** @var GetBGGDataHandler */
    private $dataHandler;

    /**
     * @param GetBGGDataHandler $dataHandler
     * @param null $name
     */
    public function __construct(GetBGGDataHandler $dataHandler, $name = null)
    {
        parent::__construct($name);

        $this->dataHandler = $dataHandler;
    }

    protected function configure()
    {
        $this
            ->setName('app:sync-bgg-data')
            ->setDescription('Imports BoardGameGeek data');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Importing BoardGameGeek data',
            '============================',
            '',
        ]);

        $total = 1000000;
        $step = 100;

        for ($i = 1; $i < $total; $i += $step) {
            $rangeString = "$i to " . ($i + $step);
            try {
                $this->dataHandler->handle(new GetBGGData(range($i, $i + $step), true, true, true));
                $output->writeln("<info>Imported boardgames $rangeString</info>");
            } catch (Exception $e) {
                $output->writeln("<error>Failed to import boardgames $rangeString</error>");
            }
            sleep(1);
        }
    }
}
