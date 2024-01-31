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

#[AsCommand(name: 'customer:create')]
class CustomerCreate extends Command
{
    public function __construct(
        #[Autowire(service: 'customer.repository')] private EntityRepository $customerRepository,
        #[Autowire(service: 'customer_group.repository')] private EntityRepository $customerGroupRepository,
        #[Autowire(service: 'country.repository')] private EntityRepository $countryRepository,
        #[Autowire(service: 'sales_channel.repository')] private EntityRepository $salesChannelRepository,
        #[Autowire(service: 'payment_method.repository')] private EntityRepository $paymentMethodRepository,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->addOption('first_name', null, InputOption::VALUE_REQUIRED, 'First name');
        $this->addOption('last_name', null, InputOption::VALUE_REQUIRED, 'Last name');
        $this->addOption('company', null, InputOption::VALUE_REQUIRED, 'Company');
        $this->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email address');
        $this->addOption('password', null, InputOption::VALUE_REQUIRED, 'Password');
        $this->addOption('street', null, InputOption::VALUE_REQUIRED, 'Street');
        $this->addOption('city', null, InputOption::VALUE_REQUIRED, 'City');
        $this->addOption('country', null, InputOption::VALUE_REQUIRED, 'Country code');

        $this->addOption('sales_channel_id', null, InputOption::VALUE_OPTIONAL, 'Sales Channel ID');
        $this->addOption('group_id', null, InputOption::VALUE_OPTIONAL, 'Customer Group ID');
        $this->addOption('payment_method_id', null, InputOption::VALUE_OPTIONAL, 'Payment Method ID');
        $this->addOption('customer_number', null, InputOption::VALUE_OPTIONAL, 'Customer Number');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $firstName = (string)$input->getOption('first_name');
        $lastName = (string)$input->getOption('last_name');
        $company = (string)$input->getOption('company');
        $email = (string)$input->getOption('email');
        $password = (string)$input->getOption('password');
        $street = (string)$input->getOption('street');
        $city = (string)$input->getOption('city');
        $countryCode = (string)$input->getOption('country');

        $salesChannelId = (string)$input->getOption('sales_channel_id');
        $groupId = (string)$input->getOption('group_id');
        $paymentMethodId = (string)$input->getOption('payment_method_id');
        $customerNumber = (string)$input->getOption('customer_number') ?? '42';

        $customerData = [
            'id' => Uuid::randomHex(),
            'firstName' => $firstName,
            'lastName' => $lastName,
            'company' => $company,
            'email' => $email,
            'password' => $password,
            'countryId' => $this->getCountryByCode($countryCode),
            'city' => $city,
            'street' => $street,
        ];

        $customerData['customerNumber'] = !empty($customerNumber) ? $customerNumber : md5($customerData['email']);
        $customerData['salesChannelId'] = !empty($salesChannelId) ? $salesChannelId : $this->getSalesChannelId();
        $customerData['groupId'] = !empty($groupId) ? $groupId : $this->getCustomerGroupId();
        $customerData['defaultPaymentMethodId'] = !empty($paymentMethodId) ? $paymentMethodId : $this->getPaymentMethodId();
        $customerData['defaultBillingAddress'] = $this->getAddress($customerData);
        $customerData['defaultShippingAddress'] = $this->getAddress($customerData);

        $this->customerRepository->create([$customerData], Context::createDefaultContext());
        $output->writeln('Customer created successfully');

        return Command::SUCCESS;
    }

    private function getSalesChannelId(): string
    {
        return $this->salesChannelRepository->searchIds(new Criteria(), Context::createDefaultContext())->firstId();
    }

    private function getCustomerGroupId(): string
    {
        return $this->customerGroupRepository->searchIds(new Criteria(), Context::createDefaultContext())->firstId();
    }

    private function getPaymentMethodId(): string
    {
        return $this->paymentMethodRepository->searchIds(new Criteria(), Context::createDefaultContext())->firstId();
    }

    private function getAddress(array $customerData): array
    {
        return array_merge($customerData, [
        ]);
    }

    private function getCountryByCode(string $countryCode): string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('iso', $countryCode));
        return $this->countryRepository->searchIds($criteria, Context::createDefaultContext())->firstId();
    }
}