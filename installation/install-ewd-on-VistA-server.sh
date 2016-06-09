#!/bin/bash -xi

# set username
myusername=$USER

# install EPEL and REMI Repos ##################
#
echo installing epel-release and remi for CentOS/RHEL 6
echo --------------------------------------------------
sudo rpm -Uvh http://dl.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
sudo rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-6.rpm
rpm -Uvh http://dev.mysql.com/get/mysql-community-release-el6-5.noarch.rpm
# sed -i s'/enabled=1/enabled=0/' /etc/yum.repos.d/remi.repo
sudo cp /vagrant/provision/remi.repo /etc/yum.repos.d/

# install Nodejs and Development Tools such as gcc & make
sudo yum -y groupinstall 'Development Tools'
sudo yum -y install nodejs npm 

# EWD.js and Federator installation ############################
sudo mkdir /var/log/raptor 
sudo touch /var/log/raptor/federatorCPM.log
sudo touch /var/log/raptor/ewdjs.log

sudo chown -R $myusername:$myusername /var/log/raptor

cd /vagrant/OtherComponents/EWDJSvistalayer
sudo cp -R ewdjs /opt/
sudo chown -R $myusername:$myusername /opt/ewdjs
cd /opt/ewdjs 

# install VA TRM approved version of ewdjs 
npm install ewdjs@0.103.0 

# THESE STEPS MUST BE HANDLED MANUALLY FOR A PRODUCTION INSTALLATION
#

## get database interface from cache version we are running
#sudo cp /srv/bin/cache0100.node /opt/ewdjs/node_modules/cache.node
#
## add ewdfederator access to EWD
#node registerEWDFederator.js
#
## start EWD and Federator 
#sudo dos2unix startEverything.sh 
#sudo chmod a+x startEverything.sh 
#sudo ./startEverything.sh 
