<?php declare(strict_types=1);

namespace Yireo\AdditionalCustomerCommands\Console\Command;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'customer:list')]
class CustomerList extends Command
{
    public function __construct(
        #[Autowire(service: 'customer.repository')] private EntityRepository $customerRepository,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);

        $results = $this->customerRepository->search(new Criteria(), Context::createDefaultContext());
        foreach ($results->getEntities() as $customer) {
            /** @var CustomerEntity $customer */
            $table->addRow([
                $customer->getId(),
                $customer->getFirstName(),
                $customer->getLastName(),
                $customer->getEmail(),
                $customer->getSalesChannelId(),
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}