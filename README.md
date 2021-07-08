## Consumer Transfer

This app contains RESTful API endpoints that allows authorised consumers make money transfers 
using a payment gateway provider in Nigeria (e.g. Paystack, Flutterwave) as well as 
being able to list & search their transfer history.

## How I Interpreted/Approched the Task
Users (Consumers) signup/login on the platform and a digital wallet is created for them during the process, this wallet can be funded, but for the purpose of this demo, all users have (NGN) 3,000.00 in their wallet when they signup. This money can then be transfered from the wallet to any bank account using paystack as the payment gateway. 

## Technology
This project was built with Laravel PHP while PHPCS and PHPStan are setup and configured in the codebase as static analysis tool to ensure clean, readable, good code quality and uniform standards across the codebase. Github Action is also setup for CI pipeline.

- To run PHPCS configuration against the codebase locally, run the command *./vendor/bin/phpcs*
- To run PHPStan configuration against the codebase locally, run the command *./vendor/bin/phpstan analyse*


## Installation Process
- Clone the project to your local machine
- Run the command *composer install*
- Run the command *php artisan key:generate*
- If .env file diesn't exist, run the command *cp .env.example .env*
- In the .env file, update the necessary information to allow connection to a database and also signup on paystack to get your *SECRET KEY*
- Run the command *php artisan migrate:fresh --seed* 
- Run the command *php artisan passport:install --uuids* to create passport's *Client ID and Client secret*


Here's the [LINK](http://143.244.156.216/) to view the project live.
Here's the [POSTMAN COLLECTION](https://documenter.getpostman.com/view/13007176/Tzm6jvKC) to view the project API documentation.

