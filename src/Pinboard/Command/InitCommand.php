<?php
namespace Pinboard\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class InitCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('register-crontab')
            ->setDescription('Init crontab for data aggregating')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Defining crontab task...</info>');
        $output->writeln('<info>Please enter the frequency of data aggregating</info> <comment>(frequency must be equal "pinba_stats_history" of the pinba engine config)</comment>.');

        $dialog = $this->getHelperSet()->get('dialog');
        $frequency = $dialog->askAndValidate(
            $output,
            'Frequency (in minutes, default "15"): ',
            function ($answer) {
                if (intval($answer) <= 0) {
                    throw new \RunTimeException(
                        'You must enter positive integer value'
                    );
                }
                return $answer;
            },
            false,
            '15'
        );

        $process = new Process('crontab -l');
        $process->setTimeout(20);
        $process->run();

        $crontabString = $process->isSuccessful() ? $process->getOutput() : '';

        $path = realpath(__DIR__ . '/../../../console');
        $command = '*/' . $frequency . ' * * * * ' . $path . ' aggregate';

        if (strpos($crontabString, $command) === false) {
            $crontabString .= "\n" . $command . "\n";
        }

        $file = tempnam(sys_get_temp_dir(), 'ipm');
        file_put_contents($file, $crontabString);

        $process = new Process('crontab ' . $file);
        $process->setTimeout(20);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $output->writeln('<info>Crontab task are defined successfully</info>');
        $output->writeln('<info>Please set parameter "aggregation_period" to value "PT' . $frequency . 'M" in config/parameters.yml</info>');
    }
}