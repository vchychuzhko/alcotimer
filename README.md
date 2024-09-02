# AlcoTimer

[![version](https://img.shields.io/badge/version-0.6.2--beta-orange)](https://alcotimer.com)

Web application to add some random into drinking process.

## Table of contents

- [Requirements](#requirements)
- [Deploying](#deploying)
- [Development](#development)

## Requirements

* Web server pointed to `pub` folder
* PHP 7.4+
* Composer 2

💡 `nginx.conf.sample` contains needed configurations, including secure connection and redirects. Replace `domain.com` and `user` placeholders with the actual data.

## Deploying

1) Install dependencies using composer:

```bash
composer install --no-dev
```

2) Build production assets:

```bash
php bin/console static:deploy frontend
```

## Development

Deploying is the same as [production deploy](#deploying), but step with dependencies installation goes **without** flags.

### Developer Mode

To use developer mode enable it in `app/etc/config.php` file:

```php
return [
    ...
    'developer_mode' => 1,
    ...
];
```

Also set **display_errors** flag to true in `app/bootstrap.php`:

```php
ini_set('display_errors', true);
```

### Assets

```bash
php bin/console watch # build and watch after assets changes
```

Make sure assets minification is disabled in `app/etc/config.php`:

```php
return [
    ...
    'web' => [
        ...
        'js' => [
            'minify' => 0,
            ...
        ],
        'css' => [
            'minify' => 0,
            ...
        ],
    ],
    ...
];
```
