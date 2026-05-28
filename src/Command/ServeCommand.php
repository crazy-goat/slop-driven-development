<?php

declare(strict_types=1);

namespace App\Command;

use App\Kernel;
use CrazyGoat\WorkermanBundle\Runtime\Runner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'microblog:serve',
    description: 'Uruchamia serwer Workerman (Slop Driven Development)',
)]
class ServeCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Kernel $kernel */
        $kernel = $this->getApplication()?->getKernel();
        \assert($kernel instanceof Kernel);

        $runner = new Runner($kernel);
        $runner->run();

        return Command::SUCCESS;
    }
}
