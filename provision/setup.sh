#!/bin/bash -xi
# set up base box through vagrant file with these commands

# disable selinux ############################
#
echo disabling ipv4 firewall
echo -----------------------
sudo service iptables save
sudo service iptables stop
sudo chkconfig iptables off
echo checking that it is disabled
/sbin/iptables -L -v -n

echo disabling ipv6 firewall
echo -----------------------
sudo service ip6tables save
sudo service ip6tables stop
sudo chkconfig ip6tables off
echo checking that it is disabled
/sbin/ip6tables -L -v -n

# install EPEL and REMI Repos ##########################
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
sudo yum -y install vim zip unzip wget drush httpd php php-gd php-mcrypt php-curl
sudo chkconfig httpd on

# install Nodejs and Development Tools such as gcc & make
sudo yum -y groupinstall 'Development Tools'
sudo yum -y install nodejs npm
sudo npm -g install bower

# copy php.ini from provision folder to prepare for Drupal 7
# 'expose_php' and 'allow_url_fopen' will be set to 'Off'
sudo cp /vagrant/provision/php.ini /etc/

# Change 'AllowOverride None' to 'All' in httpd.conf 
sudo cp /vagrant/provision/httpd.conf /etc/httpd/conf/
sudo service httpd start

#echo update repositories
#echo -------------------
#sudo yum update

# Install MySQL ###############################
#
echo install mysql
echo -------------
cd
wget http://repo.mysql.com/mysql-community-release-el6-5.noarch.rpm
# note:
# maybe better to wget the rpm's instead and put them in /vagrant/provision to install
# that way they will be available for a quicker install upon subsequent builds...
sudo rpm -Uvh mysql-community-release-el6-5.noarch.rpm
sudo yum -y install mysql mysql-server php-mysql php-soap php-mbstring php-dom php-xml rsync
sudo rpm -qa | grep mysql
sudo chkconfig mysqld on
sudo service mysqld start
export DATABASE_PASS='raptor1!'
mysqladmin -u root password "$DATABASE_PASS"
mysql -u root -p"$DATABASE_PASS" -e "UPDATE mysql.user SET Password=PASSWORD('$DATABASE_PASS') WHERE User='root'"
mysql -u root -p"$DATABASE_PASS" -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1')"
mysql -u root -p"$DATABASE_PASS" -e "DELETE FROM mysql.user WHERE User=''"
mysql -u root -p"$DATABASE_PASS" -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\_%'"
mysql -u root -p"$DATABASE_PASS" -e "FLUSH PRIVILEGES"

# set up database for drupal
mysql -u root -p"$DATABASE_PASS" -h localhost -e "create database raptor500;"
# add standard tables from a clean installation of Drupal 7
mysql -u root -p"$DATABASE_PASS" -h localhost raptor500 < /vagrant/provision/drupal.sql
# add RAPTOR specific tables
mysql -u root -p"$DATABASE_PASS" -h localhost raptor500 < /vagrant/provision/raptor_tables.sql
# add RAPTOR database user and assign access
mysql -u root -p"$DATABASE_PASS" -h localhost -e "create user raptoruser@localhost identified by 'raptor1!'"
mysql -u root -p"$DATABASE_PASS" -h localhost -e "GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,INDEX,ALTER,CREATE TEMPORARY TABLES,LOCK TABLES ON raptor500.* TO raptoruser@localhost; flush privileges;"

# DRUPAL 7 #########################################################################
#

# Install Drush ###########################################

# Download latest stable release using the code below or browse to github.com/drush-ops/drush/releases.
cd
wget http://files.drush.org/drush.phar
# Or use our upcoming release: wget http://files.drush.org/drush-unstable.phar  

# Test your install.
php drush.phar core-status

# Rename to `drush` instead of `php drush.phar`. Destination can be anywhere on $PATH. 
sudo chmod +x drush.phar
sudo mv drush.phar /usr/local/bin/drush

# Enrich the bash startup file with completion and aliases.
/usr/local/bin/drush -y init

# Get Drupal 7 ##############################################

wget http://ftp.drupal.org/files/projects/drupal-7.30.tar.gz
# wget http://ftp.drupal.org/files/projects/drupal-7.41.tar.gz
tar xzvf drupal*
cd drupal*
sudo mkdir /var/www/html/RSite500
sudo rsync -avz . /var/www/html/RSite500/
sudo mkdir /var/www/html/RSite500/sites/default/files

# RAPTOR Application ########################################

# copy RAPTOR modules and themes to drupal installation
sudo cp -R /vagrant/modules/* /var/www/html/RSite500/sites/all/modules/
sudo cp -R /vagrant/themes/* /var/www/html/RSite500/sites/all/themes/

# create tmp folder 
sudo mkdir /var/www/html/RSite500/sites/default/files/tmp

## copy Drupal as RSite500 and configure to use the raptor500 database
#cd /var/www/html
#sudo cp -R drupal RSite500

# configure RSite500 to use raptor500 database
sudo cp /vagrant/provision/settings500.php /var/www/html/RSite500/sites/default/settings.php
sudo chmod 664 /var/www/html/RSite500/sites/default/settings.php

# enable RAPTOR Modules
cd /var/www/html/RSite500/sites/all/modules/
/usr/local/bin/drush -y en raptor_contraindications raptor_graph raptor_workflow raptor_datalayer raptor_imageviewing raptor_ewdvista raptor_mdwsvista simplerulesengine_core raptor_floatingdialog raptor_protocollib simplerulesengine_demo raptor_formulas raptor_reports simplerulesengine_ui raptor_glue raptor_scheduling

# automatically download the front-end libraries used by Omega
cd /var/www/html/RSite500/sites/all/themes/omega/omega
sudo chown -R vagrant /var/www/html/RSite500/sites/all/themes/omega/omega
/usr/local/bin/drush -y make libraries.make --no-core --contrib-destination=.
sudo chmod a+rx /var/www/html/RSite500/sites/all/themes/omega/omega/libraries
sudo chown -R apache /var/www/html/RSite500/sites/all/themes/omega/omega

# enable and set raptor theme
cd /var/www/html/RSite500/sites/all/themes/

# drush -y -l http://localhost/RSite500/ pm-enable omega
/usr/local/bin/drush -y -l http://localhost/RSite500/ pm-enable raptor_omega
# drush -y -l http://localhost/RSite500/ vset theme_default raptor_omega
# drush -y -l http://localhost/RSite500/ omega-export raptor_omega

# I'm sure ownership is borked from all the sudo commands...
sudo chown -R apache:apache /var/www

# restart apache so all php modules are loaded...
sudo service httpd restart

echo RAPTOR is now installed to a test instance for site 500
echo Browse to: http://192.168.33.11/RSite500/
