# DSU-report-api
API pentru aplicatia de raportare integrata a DSU

1. Local VM setup
- install Vagrant
- install VirtualBox
- add this line to your localhost file: 
```
192.168.13.37   dsu.civictech.local
```
- git clone from the repository
- cd to the automation directory
- run the vm provisioning:
```
vagrant up
```
- browse to 
```
http://dsu.civictech.local
```
- accept certificate warning (in chrome type: badidea) and add the user and password for the basic auth from the 'secrets' var file