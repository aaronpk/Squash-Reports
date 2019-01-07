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
$ git clone --depth=1 https://github.com/iamcal/emoji-data.git public/emoji
```

Set up the queue listener to run via Supervisor or some other method:

```
$ php artisan queue:listen --tries=3
```

Set a cron job to run the scheduler:

```
*/5 * * * * cd /web/sites/squashreports.com/squash && /usr/bin/php artisan schedule:run >> /web/sites/squashreports.com/squash/storage/logs/cron.log
```
