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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            "Importing BoardGameGeek data",
            "============================",
            "",
        ]);

        for ($i = 1; $i < 1000000; $i ++) {
            try {
                $this->dataHandler->handle(new GetBGGData($i, true, true, true));
                $output->writeln("Imported boardgame $i");
            } catch (Exception $e) {
                $output->writeln("<error>Failed to import boardgame $i</error>");
            }
        }
    }
}