# About the Project
API written in PHP and Symfony with OpenApi docs, Postgres db, JWT authorization, Redis for cache and Stripe payment processor.
The API offers an overview of books, ability for admins to add, remove and update books, cart functionality implemented with Redis (carts expires after 1h after the last update), JWT for authorizing users and Stripe for payment processing.

## Frontend
For Frontend visit [this Github repo](https://github.com/eduard-negescu/bookstore-react)

## Installation
The project requires PHP > 8.2 and Docker.
* To install the Docker Container for Postgres and Redis run:
```
docker-compose up -d
```
* To install composer dependencies:
```
composer install
```
* To generate JWT keys:
```
php bin/console lexik:jwt:generate-keypair
```
* To run the DB migrations:
```
doctrine:migrations:migrate
```
* Make sure to set up the STRIPE_SECRET_KEY environment variable for the secret key to your Stripe account.
* To run a local server:
```
php -S localhost:8000 -t public
```

## OpenApi Documentation
To view the Swagger page access the api/doc route. There you can see all the routes in project explained.
[OpenApi](/screenshots/OpenApi.png)