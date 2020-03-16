# Required

- PHP 7.0+

- composer

- nginx

# Install

Configurate Nginx (example in file ./nginx.conf). Nginx config file in project dir.

```
composer install
```

# Examples

```
/api/cb/get-rate?from=EUR&to=USD&date=20/01/2020
```
```
/api/cb/get-rate?from=RUR&to=EUR&date=20/03/2020
```
```
/api/cb/get-rate?from=RUR&to=EUR&date=01/03/2020
```

# TODO

- Add API Doc

- Add autotests in Behat