# Squash Reports

Squash Reports keep your team in touch every day

## Installation

* Copy `.env.example` to `.env`
* Enter your Slack API credentials
* Enter your SMTP credentials
* Create an empty mysql database and enter the credentials

```
$ cd squash
$ composer install
$ php artisan migrate:install
$ php artisan migrate
$ sudo chown -R www-data storage
```
