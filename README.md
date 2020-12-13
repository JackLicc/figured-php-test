## Prerequisites
1. php 7.1+
2. mysql 5.6+
3. composer 1.0+

## Installation
- git clone https://github.com/JackLicc/figured-php-test.git
- cd figured-php-test
- composer install
- cp .env.example .env
- configure following mysql settings in .env file
  - DB_HOST
  - DB_PORT
  - DB_DATABASE
  - DB_USERNAME
  - DB_PASSWORD
- php artisan migrate
- load data from CSV file
  - put the csv file in "storage/app/" directory, for example "storage/app/fertiliser-inventory-movements.csv"
  - php artisan inventory:import "storage/app/fertiliser-inventory-movements.csv"

## PHPUnit tests
- ./vendor/bin/phpunit tests

## Serve with PHP build-in web server
- cd public && php -S localhost:8000
- open http://localhost:8000/inventory/index
