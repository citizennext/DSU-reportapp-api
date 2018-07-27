[![Waffle.io - Columns and their card count](https://badge.waffle.io/civictechro/DSU-reportapp-api.svg?columns=all)](https://waffle.io/civictechro/DSU-reportapp-api)  
[![Latest Release](https://img.shields.io/github/release/civictechro/DSU-reportapp-api.svg?format=flat-square)](https://github.com/civictechro/DSU-reportapp-api/releases/latest)
[![Latest Unstable Version](https://img.shields.io/badge/unstable-0.1.3-orange.svg?format=flat-square)](https://github.com/civictechro/DSU-reportapp-api/releases/tag/version/0.1.3)
[![Open Issues](https://img.shields.io/github/issues/civictechro/DSU-reportapp-api.svg?format=flat-square)](https://github.com/civictechro/DSU-reportapp-api/issues)
[![Software License](https://img.shields.io/github/license/civictechro/DSU-reportapp-api.svg?style=flat-square)](https://github.com/civictechro/DSU-reportapp-api/blob/master/LICENSE)
[![Code consistency](http://squizlabs.github.io/PHP_CodeSniffer/analysis/squizlabs/PHP_CodeSniffer/grade.svg)](https://squizlabs.github.io/PHP_CodeSniffer/analysis/civictechro/DSU-reportapp-api/)

# DSU-report-api
API pentru aplicatia de raportare integrata a DSU

Impreuna cu API-ul din acest repo, se va instala si aplicatia de administrare a API-ului [DSU-report-api-admin](https://github.com/civictechro/DSU-reportapp-api-admin)

## 1. Prepare local development environment
- install Vagrant
- install VirtualBox

- add these lines to your local `hosts` file: 
  - NOTE: Windows users -> local `hosts` file is located here C:\Windows\System32\drivers\etc\hosts; deactivate your antivirus during editing hosts file.
```
192.168.13.37   dsu.civictech.local
192.168.13.37   dsu-admin.civictech.local
```
- create local directory which will contain all project repos: [local project directory]
- git clone in [local project directory] from the repository [DSU-report-api](https://github.com/civictechro/DSU-reportapp-api)
    - NOTE: Git Workflow -> [Wiki Doc](https://github.com/civictechro/DSU-reportapp-api/wiki/Git-Workflow)
- git clone in [local project directory] from the repository [DSU-report-api-admin](https://github.com/civictechro/DSU-reportapp-api-admin)

## 2. Local VM setup and provisioning
- go to [local project directory]/DSU-reportapp-api/automation/provision/ 
- run the vm provisioning:
```
vagrant up
```

## 3. Set local environment variables for the apps
- SSH into local VM (from [local project directory]/DSU-reportapp-api/automation/provision/)
```
vagrant ssh
```
- Create .env file for DSU-reportapp-api
```
$ cd /vagrant/DSU-reportapp-api/api/
$ cp .env.example .env
```
- Create .env file for DSU-reportapp-api-admin
```
$ cd /vagrant/DSU-reportapp-api-admin/api-man/
$ cp .env.example .env
```

## 3. Retrieve dependencies
```
$ cd /vagrant/DSU-reportapp-api/api/
$ composer install

$ cd /vagrant/DSU-reportapp-api-admin/api-man/
$ composer install
```

## 4. Generate keys
```
$ cd /vagrant/DSU-reportapp-api-admin/api-man/
$ php artisan key:generate

$ cd /vagrant/DSU-reportapp-api/api/
$ php artisan key:generate
```

## 5. Create DB tables and seed test data
```
cd /vagrant/DSU-reportapp-api-admin/api-man/
$ php artisan migrate
$ php artisan db:seed
```

## 6 Test
- browse DSU-reportapp-api and accept certificate warning (in chrome type: badidea)
  - Custom Lumen landing page should be displayed
```
http://dsu.civictech.local
```

- browse DSU-reportapp-api-admin and accept certificate warning (in chrome type: badidea)
  - Should redirect to login screen http://dsu-admin.civictech.local/admin/login
  - Use test credentials from DSU-reportapp-api-admin/api-man/database/seeds/UsersTableSeeder.php
```
http://dsu-admin.civictech.local
```
