# Shopware 6 additional customer commands

**Shopware plugin to manage customer records from the CLI**

## Installation
```bash
composer require yireo/shopware6-additional-customer-commands
bin/console plugin:refresh
bin/console plugin:install YireoAdditionalCustomerCommands
bin/console cache:clear
```

## Usage
List all customers
```bash
bin/console customer:list
```

Create a new customer (and its address):
```bash
bin/console customer:create --first_name John --last_name Doe --company Shopware --email john@shopware.com --password P@ssw0rd123 --street "Ebbinghoff 10" --city Schöppingen --country DE
```

## Something missing?
Add your Pull Request and let's get started.