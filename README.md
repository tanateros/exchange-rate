# Required

- PHP 7.0+

- composer

- nginx

- cron service (for crypto add in history)

# Install

Configurate Nginx (example in file ./nginx.conf). Nginx config file in project dir.

```
composer install
```

For crypto currency history (from project directory):

```
(crontab -l 2>/dev/null || true; echo "*/1 * * * * sudo -u www-data `pwd`/cli/parseCrypto.php") | crontab -
```

# Examples CB

```
/api/cb/get-rate?from=EUR&to=USD&date=20/01/2020
```
```
/api/cb/get-rate?from=RUR&to=EUR&date=20/03/2020
```
```
/api/cb/get-rate?from=RUR&to=EUR&date=01/03/2020
```

# Examples CB

```
/api/crypto/get-rates?dateStart=22/01/2020-02:16&dateEnd=20/04/2020-03:00
```

```
/api/crypto/get-rates?dateStart=22/03/2020-02:16&dateEnd=20/04/2020-03:00
```

# TODO

- Add API Doc

- Add autotests in Behat