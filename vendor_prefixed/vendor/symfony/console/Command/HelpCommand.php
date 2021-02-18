<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WPCOM_VIP\Symfony\Component\Console\Command;

use WPCOM_VIP\Symfony\Component\Console\Helper\DescriptorHelper;
use WPCOM_VIP\Symfony\Component\Console\Input\InputArgument;
use WPCOM_VIP\Symfony\Component\Console\Input\InputInterface;
use WPCOM_VIP\Symfony\Component\Console\Input\InputOption;
use WPCOM_VIP\Symfony\Component\Console\Output\OutputInterface;
/**
 * HelpCommand displays the help for a given command.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class HelpCommand extends \WPCOM_VIP\Symfony\Component\Console\Command\Command
{
    private $command;
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->ignoreValidationErrors();
        $this->setName('help')->setDefinition([new \WPCOM_VIP\Symfony\Component\Console\Input\InputArgument('command_name', \WPCOM_VIP\Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'The command name', 'help'), new \WPCOM_VIP\Symfony\Component\Console\Input\InputOption('format', null, \WPCOM_VIP\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'The output format (txt, xml, json, or md)', 'txt'), new \WPCOM_VIP\Symfony\Component\Console\Input\InputOption('raw', null, \WPCOM_VIP\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'To output raw command help')])->setDescription('Displays help for a command')->setHelp(<<<'EOF'
The <info>%command.name%</info> command displays help for a given command:

  <info>php %command.full_name% list</info>

You can also output the help in other formats by using the <comment>--format</comment> option:

  <info>php %command.full_name% --format=xml list</info>

To display the list of available commands, please use the <info>list</info> command.
EOF
);
    }
    public function setCommand(\WPCOM_VIP\Symfony\Component\Console\Command\Command $command)
    {
        $this->command = $command;
    }
    /**
     * {@inheritdoc}
     */
    protected function execute(\WPCOM_VIP\Symfony\Component\Console\Input\InputInterface $input, \WPCOM_VIP\Symfony\Component\Console\Output\OutputInterface $output)
    {
        if (null === $this->command) {
            $this->command = $this->getApplication()->find($input->getArgument('command_name'));
        }
        $helper = new \WPCOM_VIP\Symfony\Component\Console\Helper\DescriptorHelper();
        $helper->describe($output, $this->command, ['format' => $input->getOption('format'), 'raw_text' => $input->getOption('raw')]);
        $this->command = null;
        return 0;
    }
}
