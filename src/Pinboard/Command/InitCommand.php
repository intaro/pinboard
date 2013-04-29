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
            ->setName('init')
            ->setDescription('Init report tables in database and crontab for data aggregating')
            ->addOption(
               'no-tables',
               null,
               InputOption::VALUE_NONE,
               'If set, the task will not create report database tables'
            )
            ->addOption(
               'no-indexes',
               null,
               InputOption::VALUE_NONE,
               'If set, the task will not create indexes on tables'
            )
            ->addOption(
               'no-crontab',
               null,
               InputOption::VALUE_NONE,
               'If set, the task will not define crontab task'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = $this->getApplication()->getSilex()['db'];
        
        if (!$input->getOption('no-tables')) {
            $output->writeln('<info>Creating database tables...</info>');            

            $sql = file_get_contents(__DIR__ . '/../../../config/default_tables.sql');            
            $db->query($sql);

            $output->writeln('<info>Database tables are created successfully</info>');
        }
        
        if (!$input->getOption('no-indexes')) {
            $output->writeln('<info>Creating table indexes...</info>');            

            $sql = file_get_contents(__DIR__ . '/../../../config/indexes.sql');            
            $db->query($sql);

            $output->writeln('<info>Table indexes are created successfully</info>');
        }

        if (!$input->getOption('no-crontab')) {
            $output->writeln('<info>Defining crontab task...</info>');            
            $output->writeln('<info>Please enter the frequency of data aggregating</info> <comment>(frequency must be equal "pinba_stats_history" of the pinba engine config)</comment>.');            
            
            $dialog = $this->getHelperSet()->get('dialog');
            $frequency = $dialog->askAndValidate(
                $output,
                'Frequency (in minutes, default "5"): ',
                function ($answer) {
                    if (intval($answer) <= 0) {
                        throw new \RunTimeException(
                            'You must enter positive integer value'
                        );
                    }
                    return $answer;
                },
                false,
                '5'
            );
            
            $process = new Process('crontab -l');
            $process->setTimeout(20);
            $process->run();
            
            $crontabString = $process->isSuccessful() ? $process->getOutput() : '';
            
            $command = '*/' . $frequency . ' * * * * ' . __DIR__ . '/../../../console aggregate';
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
        }
    }
}