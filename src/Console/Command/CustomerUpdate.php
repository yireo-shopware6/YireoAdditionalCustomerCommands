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

#[AsCommand(name: 'customer:update')]
class CustomerUpdate extends Command
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

        $this->addOption('first_name', null, InputOption::VALUE_OPTIONAL, 'First name');
        $this->addOption('last_name', null, InputOption::VALUE_OPTIONAL, 'Last name');
        $this->addOption('company', null, InputOption::VALUE_OPTIONAL, 'Company');
        $this->addOption('password', null, InputOption::VALUE_OPTIONAL, 'Password');
        $this->addOption('sales_channel_id', null, InputOption::VALUE_OPTIONAL, 'Sales Channel ID');
        $this->addOption('group_id', null, InputOption::VALUE_OPTIONAL, 'Customer Group ID');
        $this->addOption('customer_number', null, InputOption::VALUE_OPTIONAL, 'Customer Number');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (string)$input->getOption('email');
        if (empty($email)) {
            $output->writeln('<error>No email given</error');
            return Command::FAILURE;
        }

        $criteria = new Criteria;
        $criteria->addFilter(new EqualsFilter('email', $email));
        $context = Context::createDefaultContext();
        $customerId = $this->customerRepository->searchIds($criteria, $context)->firstId();

        if (empty($customerId)) {
            $output->writeln('<error>No customer found</error');
            return Command::FAILURE;
        }

        $firstName = (string)$input->getOption('first_name');
        $lastName = (string)$input->getOption('last_name');
        $company = (string)$input->getOption('company');
        $password = (string)$input->getOption('password');
        $salesChannelId = (string)$input->getOption('sales_channel_id');
        $groupId = (string)$input->getOption('group_id');
        $customerNumber = (string)$input->getOption('customer_number') ?? '42';

        $customerData = [
            'id' => $customerId,
        ];

        if (!empty($firstName)) {
            $customerData['firstName'] = $firstName;
        }

        if (!empty($lastName)) {
            $customerData['lastName'] = $lastName;
        }

        if (!empty($company)) {
            $customerData['company'] = $company;
        }

        if (!empty($email)) {
            $customerData['email'] = $email;
        }

        if (!empty($password)) {
            $customerData['password'] = $password;
        }

        if (!empty($customerNumber)) {
            $customerData['customerNumber'] = $customerNumber;
        }

        if (!empty($salesChannelId)) {
            $customerData['salesChannelId'] = $salesChannelId;
        }

        if (!empty($groupId)) {
            $customerData['groupId'] = $groupId;
        }

        $this->customerRepository->create([$customerData], Context::createDefaultContext());
        $output->writeln('Customer created successfully');

        return Command::SUCCESS;
    }
}