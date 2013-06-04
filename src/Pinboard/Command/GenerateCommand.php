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

class GenerateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('adduser')
            ->setDescription('Generate string of parametrs')
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

        $users = $yaml['secure']['users'];
        if ($hosts)
        {
            $users[$username] = array(
                'password' => $encodePassword,
                'roles' => 'ROLE_USER',
                'hosts' => $hosts,
            );
        }
        else
        {
            $users[$username] = array(
                'password' => $encodePassword,
                'roles' => 'ROLE_USER',
            );
        }

        $newYaml = array(
            'db' => $yaml['db'],
            'logging' => $yaml['logging'],
            'locale' => $yaml['locale'],
            'pagination' => $yaml['pagination'],
            'secure' => array(
                'enable' => $yaml['secure']['enable'],
                'users' => $users,
            ),
        );

        $dumper = new Dumper();
        $newFile = $dumper->dump($newYaml, 5);

        if (!copy($filename, $filename . '~old')) {
            $output->writeln("Error during the backup configuration file");
        }
        else {
            file_put_contents($filename, $newFile);
            $output->writeln("The configuration file is updated");
        }
    }
}