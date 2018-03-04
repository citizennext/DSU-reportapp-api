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
