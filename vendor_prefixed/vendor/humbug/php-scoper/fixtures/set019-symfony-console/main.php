<?php

declare (strict_types=1);
namespace WPCOM_VIP;

use WPCOM_VIP\PhpParser\NodeDumper;
use WPCOM_VIP\PhpParser\ParserFactory;
use WPCOM_VIP\Symfony\Component\Console\Application;
use WPCOM_VIP\Symfony\Component\Console\Command\Command;
use WPCOM_VIP\Symfony\Component\Console\Input\InputInterface;
use WPCOM_VIP\Symfony\Component\Console\Output\OutputInterface;
require_once __DIR__ . '/vendor/autoload.php';
class HelloWorldCommand extends \WPCOM_VIP\Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this->setName('hello:world')->setDescription('Outputs \'Hello World\'');
    }
    protected function execute(\WPCOM_VIP\Symfony\Component\Console\Input\InputInterface $input, \WPCOM_VIP\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $output->writeln('Hello world!');
    }
}
\class_alias('WPCOM_VIP\\HelloWorldCommand', 'HelloWorldCommand', \false);
$command = new \WPCOM_VIP\HelloWorldCommand();
$application = new \WPCOM_VIP\Symfony\Component\Console\Application();
$application->add($command);
$application->setDefaultCommand($command->getName());
$application->run();
