[![Waffle.io - Columns and their card count](https://badge.waffle.io/civictechro/DSU-reportapp-api.svg?columns=all)](https://waffle.io/civictechro/DSU-reportapp-api)  
[![Latest Release](https://img.shields.io/github/release/civictechro/DSU-reportapp-api.svg?format=flat-square)](https://github.com/civictechro/DSU-reportapp-api/releases/latest)
[![Latest Unstable Version](https://img.shields.io/badge/unstable-0.1.3-orange.svg?format=flat-square)](https://github.com/civictechro/DSU-reportapp-api/releases/tag/version/0.1.3)
[![Open Issues](https://img.shields.io/github/issues/civictechro/DSU-reportapp-api.svg?format=flat-square)](https://github.com/civictechro/DSU-reportapp-api/issues)
[![Software License](https://img.shields.io/github/license/civictechro/DSU-reportapp-api.svg?style=flat-square)](https://github.com/civictechro/DSU-reportapp-api/blob/master/LICENSE)

# DSU-report-api
API pentru aplicatia de raportare integrata a DSU

## 1. Local (API) VM setup
- install Vagrant
- install VirtualBox
- add this line to your localhost file: 
  - NOTE: Windows users -> localhost file is located here C:\Windows\System32\drivers\etc\hosts; deactivate your antivirus during editing hosts file.
```
192.168.13.37   dsu.civictech.local
```
- git clone from the repository (DSU-reportapp-api)
    - NOTE: Git Workflow -> [Wiki Doc](https://github.com/civictechro/DSU-reportapp-api/wiki/Git-Workflow)
- cd to the automation directory
- run the vm provisioning:
```
vagrant up
```
- browse to 
```
http://dsu.civictech.local
```
- accept certificate warning (in chrome type: badidea) and add the user and password for the basic auth from the 'secrets' var file (automation/vars/local_secrets.yml)


## 2. Add Admin to local VM setup
- cd in the same "git projects" directory where api repo was cloned before (not inside the dir containing the .git)
- as a new project, git clone the admin repo (DSU-reportapp-api-admin) near the one for the api (eg. have them in /path/to/git-dirs/DSU-reportapp-api-admin and /path/to/git-dirs/DSU-reportapp-api)

- also add this line to your localhost file: 
```
192.168.13.37   dsu-admin.civictech.local
```
- browse to 
```
http://dsu-admin.civictech.local
```
