<?php

declare(strict_types=1);

/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
*/

namespace CoreShop\Payum\PostFinanceBundle\Command;

use CoreShop\Payum\PostFinanceBundle\Invalidator\TokenInvalidatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class TokenInvalidationCommand extends Command
{

    public function __construct(protected TokenInvalidatorInterface $tokenInvalidator, protected int $days = 0)
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this
            ->setName('postfinance:invalidate-expired-tokens')
            ->setDescription(\sprintf('Invalid Payment Tokens which are older than %d days', $this->days))
            ->addOption(
                'days', 'days',
                InputOption::VALUE_OPTIONAL,
                'Older than given days'
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $days = $this->days;

        if ($input->getOption('days')) {
            $days = (int) $input->getOption('days');
        }

        $output->writeln(\sprintf('Invalidate Tokens older than %d days.', $days));
        $this->tokenInvalidator->invalidate($days);

        return Command::SUCCESS;
    }
}
