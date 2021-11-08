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

Connect as `root` to the database from docker-compose and create a new db for the project (check `config/app_local.php` file for the credentials)
