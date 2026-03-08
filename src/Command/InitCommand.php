<?php
namespace App\Command;

use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'register-crontab', description: 'Init crontab for data aggregating')]
class InitCommand extends Command
{
    protected function configure()
    {
        $this->setDescription('Init crontab for data aggregating');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Defining crontab task...</info>');
        $output->writeln('<info>Please enter the frequency of data aggregating</info> <comment>(frequency must be equal "pinba_stats_history" of the pinba engine config)</comment>.');

        $helper = $this->getHelper('question');
        $question = new Question('Frequency (in minutes, default "15"): ', '15');
        $question->setValidator(function ($answer) {
            if ((int)$answer <= 0) {
                throw new RuntimeException('You must enter positive integer value');
            }

            return (int)$answer;
        });
        $frequency = (int)$helper->ask($input, $output, $question);

        $process = new Process(['crontab', '-l']);
        $process->setTimeout(20);
        $process->run();

        $crontabString = $process->isSuccessful() ? $process->getOutput() : '';

        $path = realpath(\dirname(__DIR__, 2) . '/bin/console');
        if ($path === false) {
            $output->writeln('<error>Unable to resolve console path</error>');
            return Command::FAILURE;
        }
        $command = '*/' . $frequency . ' * * * * ' . $path . ' aggregate';

        if (strpos($crontabString, $command) === false) {
            $crontabString .= "\n" . $command . "\n";
        }

        $file = tempnam(sys_get_temp_dir(), 'ipm');
        file_put_contents($file, $crontabString);

        $process = new Process(['crontab', $file]);
        $process->setTimeout(20);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        $output->writeln('<info>Crontab task are defined successfully</info>');
        $output->writeln('<info>Please set APP_AGGREGATION_PERIOD=PT' . $frequency . 'M in .env.local</info>');

        return Command::SUCCESS;
    }
}
