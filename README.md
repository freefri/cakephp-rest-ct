# CakePHP Application Skeleton

![Build Status](https://github.com/cakephp/app/actions/workflows/ci.yml/badge.svg?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/cakephp/app.svg?style=flat-square)](https://packagist.org/packages/cakephp/app)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%207-brightgreen.svg?style=flat-square)](https://github.com/phpstan/phpstan)

A skeleton for creating applications with [CakePHP](https://cakephp.org) 4.x.

# Serve
Run from [docker-compose](https://docs.docker.com/compose/install/):

```
docker-compose -f ./docker-compose-dev.yml up -d
```

Connect as `root` to the database launched using docker-compose (e.g. you can run exec on the container from nginx) and create a new `default` db for the project
(check `config/app_local.php` file for the credentials)

Connect to the nginx container using exec and run composer (do not run as root, but in order to set folder permissions root may be needed)

```
cd /var/www/cplatform/public/app_rest/
su composeruser
composer install
```

# Work with docker

Connect via ssh to dockerssh@localhost:2222 and the password: password

The local path is the location of this readme file and it should be mapped to `/var/www/cplatform/public`

When running test use `/var/www/cplatform/public/app_rest/phpunit.xml.dist` as default configuration file.
Also add `/var/www/cplatform/public/app_rest/vendor/autoload.php` as a default autoload file
