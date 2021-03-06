## Prerequisites
1. php 7.1+
2. mysql 5.6+
3. composer 1.0+

## Installation
1. `git clone https://github.com/JackLicc/figured-php-test.git`
2. `cd figured-php-test`
3. `composer install`
4. `cp .env.example .env`
5. configure following mysql settings in .env file
      - DB_HOST
      - DB_PORT
      - DB_DATABASE
      - DB_USERNAME
      - DB_PASSWORD
6. `php artisan key:generate`
7. `php artisan migrate`
8. load data from CSV file
      - put the csv file in "storage/app/" directory, for example "storage/app/fertiliser-inventory-movements.csv"
      - `php artisan inventory:import "storage/app/fertiliser-inventory-movements.csv"`

## PHPUnit tests
- `./vendor/bin/phpunit tests`

## Serve with PHP build-in web server
- `cd public && php -S localhost:8000`
- open http://localhost:8000/inventory/index
