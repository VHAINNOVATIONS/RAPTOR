#!/bin/bash -xi

# set username
myusername=$USER
# set up base box through vagrant file with these commands
cacheUrl=http://resources.vaftl.us/files/cache
cacheInstallerPath=/vagrant/provision/cache
cacheInstaller=cache-2014.1.3.775.14809-lnxrhx64.tar.gz
cacheInstallerSource=$cacheUrl/$cacheInstaller
parametersIsc=parameters.isc 
cacheInstallTargetPath=/srv 
# configure linux ###################
#
echo configuring ipv4 firewall
echo -----------------------
sudo service iptables stop
sudo cp /vagrant/provision/iptables /etc/sysconfig/
sudo service iptables start 

# install EPEL and REMI Repos ##################
#
echo installing epel-release and remi for CentOS/RHEL 6
echo --------------------------------------------------
sudo rpm -Uvh http://dl.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
sudo rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-6.rpm
rpm -Uvh http://dev.mysql.com/get/mysql-community-release-el6-5.noarch.rpm
# sed -i s'/enabled=1/enabled=0/' /etc/yum.repos.d/remi.repo
sudo cp /vagrant/provision/remi.repo /etc/yum.repos.d/

# Install Apache, PHP, and other tidbits ##############
#
echo installing apache, php, and other tidbits
sudo yum -y install parted vim zip unzip wget drush httpd php php-gd php-mcrypt php-curl
sudo chkconfig httpd on

# install Nodejs and Development Tools such as gcc & make
sudo yum -y groupinstall 'Development Tools'
#sudo yum -y install nodejs-6.0 npm

# Node.js v6.x

# Install NVM

cd ~
curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.32.1/install.sh | bash
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh" # This loads nvm
nvm install 6

sudo ln -s /usr/local/bin/node /usr/bin/node
sudo ln -s /usr/local/lib/node /usr/lib/node
sudo ln -s /usr/local/bin/npm /usr/bin/npm
sudo ln -s /usr/local/bin/node-waf /usr/bin/node-waf
n=$(which node);n=${n%/bin/node}; chmod -R 755 $n/bin/*; sudo cp -r $n/{bin,lib,share} /usr/local


# sudo npm -g install bower

# copy php.ini from provision folder to prepare for Drupal 7
# 'expose_php' and 'allow_url_fopen' will be set to 'Off'
sudo cp /vagrant/provision/php.ini /etc/

# Change 'AllowOverride None' to 'All' in httpd.conf 
sudo cp /vagrant/provision/httpd.conf /etc/httpd/conf/
sudo service httpd start

# Install MySQL ######################
#
echo install mysql
echo -------------
cd
wget -nc --progress=bar:force http://repo.mysql.com/mysql-community-release-el6-5.noarch.rpm
sudo rpm -Uvh mysql-community-release-el6-5.noarch.rpm
sudo yum -y install dos2unix mysql mysql-server php-mysql php-soap php-mbstring php-dom php-xml rsync ruby-devel
sudo rpm -qa | grep mysql
sudo chkconfig mysqld on
sudo service mysqld start
export DATABASE_PASS='raptor1!'
mysqladmin -u root password "$DATABASE_PASS"
mysql -u root -p"$DATABASE_PASS" -e "UPDATE mysql.user SET Password=PASSWORD('$DATABASE_PASS') WHERE User='root'"
mysql -u root -p"$DATABASE_PASS" -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1')"
mysql -u root -p"$DATABASE_PASS" -e "DELETE FROM mysql.user WHERE User=''"
mysql -u root -p"$DATABASE_PASS" -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\_%'"

# set up database for Drupal 7
mysql -u root -p"$DATABASE_PASS" -h localhost -e "create database raptor500;"
# add standard tables from a clean installation of Drupal 7
mysql -u root -p"$DATABASE_PASS" -h localhost raptor500 < /vagrant/provision/drupal.sql
# add RAPTOR database user and assign access
mysql -u root -p"$DATABASE_PASS" -h localhost -e "create user raptoruser@localhost identified by '$DATABASE_PASS';"
mysql -u root -p"$DATABASE_PASS" -h localhost -e "GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,INDEX,ALTER,CREATE TEMPORARY TABLES,LOCK TABLES ON raptor500.* TO raptoruser@localhost;"
mysql -u root -p"$DATABASE_PASS" -h localhost -e "FLUSH PRIVILEGES;"

# DRUPAL 7 ################################
#

# Install Drush ###########################

# Download latest stable release using the code below or browse to github.com/drush-ops/drush/releases.
cd
wget -nc --progress=bar:force http://files.drush.org/drush.phar

# Test your install.
php drush.phar core-status

# Rename to `drush` instead of `php drush.phar`. Destination can be anywhere on $PATH. 
sudo chmod +x drush.phar
sudo mv drush.phar /usr/local/bin/drush

# Enrich the bash startup file with completion and aliases.
/usr/local/bin/drush -y init

# Get Drupal 7 ###########################

wget -nc --progress=bar:force http://ftp.drupal.org/files/projects/drupal-7.41.tar.gz
tar xzvf drupal*
cd drupal*
sudo mkdir /var/www/html/RSite500
sudo rsync -avz . /var/www/html/RSite500/
sudo mkdir /var/www/html/RSite500/sites/default/files

# RAPTOR Application #####################

# copy RAPTOR modules and themes to drupal installation
sudo cp -R /vagrant/modules/* /var/www/html/RSite500/sites/all/modules/
sudo cp -R /vagrant/themes/* /var/www/html/RSite500/sites/all/themes/

# create tmp folder 
sudo mkdir /var/www/html/RSite500/sites/default/files/tmp

# configure RSite500 to use raptor500 database
sudo cp /vagrant/provision/settings500.php /var/www/html/RSite500/sites/default/settings.php
sudo chmod 664 /var/www/html/RSite500/sites/default/settings.php

# set permissions so $myusername has access to write
sudo chown $myusername -R /var/www/html/RSite500

# remove the $ from the end of this file which causes drush to fail
sudo sed -i '$ d' /root/.drush/drushrc.php

# enable RAPTOR Modules
cd /var/www/html/RSite500/sites/all/modules/
/usr/local/bin/drush -y en raptor_contraindications raptor_graph raptor_workflow raptor_datalayer raptor_imageviewing raptor_ewdvista raptor_mdwsvista simplerulesengine_core raptor_floatingdialog raptor_protocollib simplerulesengine_demo raptor_formulas raptor_reports simplerulesengine_ui raptor_glue raptor_scheduling

# automatically download the front-end libraries used by Omega
cd /var/www/html/RSite500/sites/all/themes/omega/omega
#sudo chown -R vagrant /var/www/html/RSite500/sites/all/themes/omega/omega
sudo chown -R $myusername /var/www/html/RSite500/sites/all/themes/omega/omega
/usr/local/bin/drush -y make libraries.make --no-core --contrib-destination=.
sudo chmod a+rx /var/www/html/RSite500/sites/all/themes/omega/omega/libraries
sudo chown -R apache /var/www/html/RSite500/sites/all/themes/omega/omega

# enable and set raptor theme
cd /var/www/html/RSite500/sites/all/themes/
/usr/local/bin/drush -y -l http://localhost/RSite500/ pm-enable raptor_omega

# I'm sure ownership is borked from all the sudo commands...
sudo chown -R apache:apache /var/www

# restart apache so all php modules are loaded...
sudo service httpd restart

# cache specific installation steps

# get ruby deps for installing cache through ruby
sudo gem install -q json -v '1.8.3'
sudo gem install -q expect -v '0.0.13'

# add group to use for cache admins to start and stop
sudo groupadd cacheserver

# get cache installer
if [ -e "$cacheInstallerPath/$cacheInstaller" ]; then
  echo "Cache installer is already in present..."
else
  echo "downloading Cache installer..."
  #wget -P $cacheInstallerPath/ http://vaftl.us/vagrant/cache-2014.1.3.775.14809-lnxrhx64.tar.gz
  #wget -nc --progress=bar:force -P $cacheInstallerPath/ http://vaftl.us/vagrant/cache-2014.1.3.775.14809-lnxrhx64.tar.gz
  wget -nc --progress=bar:force -P $cacheInstallerPath/ $cacheInstallerSource
fi

echo "Attempting to install Intersystems Caché..."
if [ -e "$cacheInstallerPath/$cacheInstaller" ]; then
  echo "Installing Cache from: $cacheInstaller"
  # install from tar.gz 
  sudo mkdir -p $cacheInstallTargetPath/tmp
  cd $cacheInstallTargetPath/tmp
  sudo cp $cacheInstallerPath/$cacheInstaller .
  sudo tar -xzvf $cacheInstaller   

  # spawn ruby cache installer
  #
  # If you need to change settings... 
  # ~see config.local.json under /vagrant/provision 
  #
  sudo mkdir /srv/mgr
  sudo cp /vagrant/provision/cache/cache.key /srv/mgr/
  cd /vagrant/provision
  dos2unix install-cache.rb
  sudo chmod u+x install-cache.rb
  echo "Running ruby installer for cache in lieu of using parameter file : Fix for deploying under Windows OS"
  sudo ruby ./install-cache.rb 
  echo "waiting..."
  sleep 60*3
else
  echo "You are missing: $cacheInstaller"
  echo "You cannot provision this system until you have downloaded Intersystems Cache"
  echo "in 64-bit tar.gz format and placed it under the provision/cache folder."
  exit
fi

# add vista and vagrant to cacheusr group
sudo usermod -a -G cacheusr vagrant

## add disk to store CACHE.DAT was sdb 
#parted /dev/sdb mklabel msdos
#parted /dev/sdb mkpart primary 0 100%
#mkfs.xfs /dev/sdb1
#mkdir /srv
#echo `blkid /dev/sdb1 | awk '{print$2}' | sed -e 's/"//g'` /srv   xfs   noatime,nobarrier   0   0 >> /etc/fstab
#mount /srv

# stop cache
sudo ccontrol stop cache quietly

if [ -e "$cacheInstallerPath/CACHE.DAT" ]; then
  echo "CACHE.DAT is already present... copying to /srv/mgr/"
  echo "This will take a while... Get some coffee or a cup of tea..."
  sudo mkdir -p $cacheInstallTargetPath/mgr/VISTA
  sudo cp -R $cacheInstallerPath/CACHE.DAT /srv/mgr/VISTA/
else
  echo "$cacheinstallerpath/CACHE.DAT not found... downloading..."
  sudo mkdir -p $cacheInstallTargetPath/mgr/VISTA 
  sudo chown -R $myusername:cacheusr $cacheInstallTargetPath/mgr/VISTA
  echo "This will take a while... Get some coffee or a cup of tea..."
  wget -nc --progress=bar:force -P $cacheInstallTargetPath/mgr/VISTA/ http://vaftl.us/vagrant/CACHE.DAT 
fi

echo "Setting permissions on database."
sudo chown -R vagrant:cacheusr /srv
sudo chmod g+wx /srv/bin
sudo chmod 775 /srv/mgr/VISTA 
sudo chmod 660 /srv/mgr/VISTA/CACHE.DAT
sudo chown -R vagrant:cacheusr /srv/mgr/VISTA 

# copy cache configuration
echo "Copying cache.cpf configuration file..."
sudo cp $cacheInstallerPath/cache.cpf $cacheInstallTargetPath/

# start cache 
sudo ccontrol start cache

# enable cache' os authentication and %Service_CallIn required by EWD.js 
csession CACHE -U%SYS <<EOE
vagrant
innovate
s rc=##class(Security.System).Get("SYSTEM",.SP),d=SP("AutheEnabled") f i=1:1:4 s d=d\2 i i=4 s r=+d#2
i 'r s NP("AutheEnabled")=SP("AutheEnabled")+16,rc=##class(Security.System).Modify("SYSTEM",.NP)

n p
s p("Enabled")=1
D ##class(Security.Services).Modify("%Service_CallIn",.p)

h
EOE

# install VEFB_1_2 ~RAPTOR Specific KIDS into VistA
# todo: this doesn't work because it doesn't see device(0) ~something with c-vt320? vt320 doesn't 
# work either...
cp /vagrant/OtherComponents/VistAConfig/VEFB_1_2.KID /srv/mgr/
cd /opt/vagrant/provision/
dos2unix install-vefb.rb
sudo chmod u+x install-vefb.rb
sudo ./install-vefb.rb

cp /vagrant/OtherComponents/EWDJSvistalayer/mumps/vefbrc.ro /srv/mgr/
#sudo chmod u+x install-vefbroutine.rb
#sudo ./install-vefbroutine.rb
#csession CACHE <<EOE
#vagrant
#innovate
#ZN VISTA
#d ^%RI
#/srv/mgr/vefbrc.ro
#A
#h
#EOE

# EWD.js and Federator installation ############################
sudo mkdir /var/log/raptor 
sudo touch /var/log/raptor/federatorCPM.log
sudo touch /var/log/raptor/ewdjs.log
sudo chown -R vagrant:vagrant /var/log/raptor

cd /vagrant/OtherComponents/EWDJSvistalayer
sudo cp -R ewdjs /opt/
sudo chown -R vagrant:vagrant /opt/ewdjs

cd /vagrant/provision
dos2unix install-ewd.rb
sudo chmod u+x install-ewd.rb
sudo ./install-ewd.rb

cd /opt/ewdjs
sudo npm install -g inherits@2.0.0
npm install globalsjs@0.31.0

# get database interface from cache version we are running
#sudo cp /srv/bin/cache0100.node /opt/ewdjs/node_modules/cache.node


# copy node_modules for ewd into RAPTOR Module space...
#cd /opt/ewdjs/node_modules/ewdjs/essentials
#sudo cp -R node_modules /var/www/html/RSite500/sites/all/modules/raptor_glue/core/
sudo cp -R /opt/ewdjs/node_modules /var/www/html/RSite500/sites/all/modules/raptor_glue/core/node_modules 
sudo chown -R apache:apache /var/www/html/RSite500/sites/all/modules/raptor_glue/core/node_modules/

# start EWD and EWD Federator
cd /opt/ewdjs

# add ewdfederator access to EWD
node registerEWDFederator.js

sudo dos2unix startEverything.sh 
sudo chmod a+x startEverything.sh 
sudo dos2unix killEverything.sh 
sudo chmod a+x killEverything.sh

# start EWD and Federator 
sudo ./startEverything.sh 

# user notifications 
echo VistA is now installed.  

echo CSP is here: http://192.168.33.11:57772/csp/sys/UtilHome.csp
echo username: cache password: innovate 
echo See Readme.md from root level of this repository... 
echo EWD Monitor: http://192.168.33.11:8082/ewd/ewdMonitor/index.html password: innovate 
echo EWD: http://192.168.33.11:8082/ewdjs/EWD.js ewdBootstrap3.js 
echo EWD Federator: http://192.168.33.11:8081/RaptorEwdVista/raptor/
echo password: innovate 
echo RAPTOR is now installed to a test instance for site 500
echo Browse to: http://192.168.33.11/RSite500/ after completing steps 1-3...
echo to kill EWD and Federator sudo sh /opt/ewdjs/killEverything.sh 
