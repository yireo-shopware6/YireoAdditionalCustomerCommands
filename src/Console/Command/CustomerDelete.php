<?php declare(strict_types=1);

namespace Yireo\AdditionalCustomerCommands\Console\Command;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'customer:delete')]
class CustomerDelete extends Command
{
    public function __construct(
        #[Autowire(service: 'customer.repository')] private EntityRepository $customerRepository,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email address');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createDefaultContext();

        $email = (string)$input->getOption('email');
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('email', $email));

        $customerIds = $this->customerRepository->searchIds($criteria, $context)->getIds();
        if (empty($customerIds)) {
            $output->writeln('<error>No customer record found</error>');
            return Command::FAILURE;
        }

        $deleteData = [];
        foreach($customerIds as $customerId) {
            $deleteData[] = ['id' => $customerId];
        }

        $this->customerRepository->delete($deleteData, $context);
        $output->writeln('Customer deleted successfully');

        return Command::SUCCESS;
    }
}