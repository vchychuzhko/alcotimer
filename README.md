# AlcoTimer
*0.5.3-beta*

Web application for people, who would like to make drinking process become really challenging.  

### Requirements
* Web server (Apache/Nginx) pointed to `pub` folder
* PHP 7.1+
* Composer

### Installation
* Install dependencies using composer:
```bash
composer install
```

* File `nginx.conf.sample` contain needed configurations, including secure connection and redirects.  
Just replace 'domain.com' for you needed domain.

### Usage
Run below command to see possible console commands:  
```bash
php bin/console help:show
``` 

###### Started as a web equivalent of [Android app](https://bitbucket.org/vchychuzhko/alcotimer)
