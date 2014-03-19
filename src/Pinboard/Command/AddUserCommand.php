<?php
namespace Pinboard\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Yaml;

class AddUserCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('add-user')
            ->setDescription('Add section for new user in configuration file')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'User name'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'User password in plain text'
            )
            ->addArgument(
                'hosts',
                InputArgument::OPTIONAL,
                'Regexp string - hosts, allowed for this user'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $hosts = $input->getArgument('hosts');

        $passwordGenerator = new MessageDigestPasswordEncoder();
        $salt = "";
        $encodePassword = $passwordGenerator->encodePassword($password, $salt);

        $filename = __DIR__ . '/../../../config/parameters.yml';
        $yaml = Yaml::parse($filename);

        $users = isset($yaml['secure']['users']) ? $yaml['secure']['users'] : array();
        if ($hosts)
        {
            if ($hosts == "") {
                $hosts = ".*";
            }

            preg_match("/" . $hosts . "/", "my test string for regexp");
            if (preg_last_error() != PREG_NO_ERROR)
            {
                $output->writeln("<error>Wrong regular expression! Code " . preg_last_error() . "</error>");
                return;
            }

            $users[$username] = array(
                'password' => $encodePassword,
                'hosts' => $hosts,
            );
        }
        else
        {
            $users[$username] = array(
                'password' => $encodePassword,
            );
        }

        $newYaml = array();
        //copy other sections
        foreach($yaml as $key => $section) {
            if ($key != 'secure') {
                $newYaml[$key] = $section;
            }
        }

        $newYaml['secure'] = array(
            'enable' => isset($yaml['secure']['enable']) ? $yaml['secure']['enable'] : true,
            'users' => $users,
        );

        $dumper = new Dumper();
        $newFile = $dumper->dump($newYaml, 5);

        $oldFilename = $filename . '~' . substr(md5(rand()), 0, 5);
        if (!copy($filename, $oldFilename)) {
            $output->writeln("<error>Error during the backup configuration file</error>");
        }
        else {
            $output->writeln("<info>Old configuration has been saved to file $oldFilename</info>");
            file_put_contents($filename, $newFile);
            $output->writeln("<info>The configuration file is updated successfully</info>");
        }
    }
}