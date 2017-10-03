#!/bin/bash
# --------------------------------------
# Adge Denkers
# adge@vaftl.us
# Future Technology Laboratory
# U.S. Department of Veterans Affairs
# project: RAPTOR
# file name: setup-on-vista-ahd.sh
# date: 2017-09-28
# --------------------------------------

echo 'starting: RAPTOR (setup-on-vista-ahd.sh)'

# --------------------------------------
# Include ahd-functions
source ahd-functions.sh
# -------------------------------------- 
logit 'starting: RAPTOR (setup-on-vista-ahd.sh)'
# --------------------------------------

# Install Nano before you do anything else
sudo yum -y install nano

# --------------------------------------
# Get RAPTOR Source from GitHub
cd

# Note: Downloading the RAPTOR Source may take a few minutes
git clone https://github.com/VHAINNOVATIONS/RAPTOR.git
# --------------------------------------

##### Setup Variables #####
echo --------------------------------------
echo /// Setup Variables
echo --------------------------------------

echo Determining server installation type
# if systemtype = "production" then it is a production system.
systemType=$1

if [ -z $systemType ]
# if length of $systemType = 0, then true
# note: true = 0, false = 1
then
    # length = 0, meaning systemType = 'new-install', meaning 'true'
    systemType="new-install"
else
    # length > 0, meaning systemType = 'production', meaning 'false'
    systemType="production"
fi

echo ""
echo ""
echo installing on system type "$systemType"



########## AHD OMITTED 11:03 PM
# myusername=$USER
myusername="ec2-user"
##########

echo myusername = $myusername

vistaServerRoot="/srv"
echo vistaServerRoot = $vistaServerRoot
vistaFilesDir="/srv/mgr"
echo vistaFilesLocation = $vistaFilesDir
vistaDbNamespace="GOLD"
echo vistaDbNamespace = $vistaDbNamespace
vistaDatFile="CACHE.DAT"
echo vistaDatFile = $vistaDatFile
echo DAT File Full Location = $vistaFilesDir/$vistaDbNamespace/$vistaDatFile

echo --------------------------------------

echo ""
echo ""

echo --------------------------------------
echo /// Install pre-requisite software...
echo --------------------------------------

##### Enable the RedHat Optional Repository #####
echo /// Enable the \'optional\' repository for RHEL
echo ""
sudo yum-config-manager --enable rhui-REGION-rhel-server-optional
echo ""
echo ""
echo See the section \"Enabling the RedHat Optional Repository\" in the file \"README-ahd.md\" for information on enabling the RedHat Optional Repository if this step fails.
echo ""

echo ""
##### Install Pre-requisite Software Packages #####
echo /// Install Pre-requisite Software Packages
sudo yum -y install git wget unzip zip ruby gcc gcc-c++ nano make dos2unix ruby-devel httpd php php-gd php-mcrypt php-curl

echo ""
##### Install Ruby Gems #####
echo /// Install Ruby Gems 
sudo gem install -q json -v '1.8.3'
sudo gem install -q expect -v '0.0.13'

##### Install NodeJS and NPM #####
#curl -sL https://rpm.nodesource.com/setup_8.x | sudo -E bash -
#sudo yum -y install nodejs

##### Install all the needed Development Tools
sudo yum -y groupinstall 'Development Tools'

##### Install NVM and NodeJS #####
cd ~
curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.32.1/install.sh | bash

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh"

nvm install 6
nvm use 6



##### ----------------------------------------------------------------------------------
##### ----------------------------------------------------------------------------------
##### ----------------------------------------------------------------------------------



########## AHD OMITTED 11:03 PM
##### Copy over php.ini #####
#sudo cp ~/RAPTOR/provision/php.ini /etc/
##########


##### Start and Setup Apache (httpd) #####
sudo service httpd start
sudo systemctl enable httpd
sudo chkconfig httpd on

echo ""
echo Pre-requisite software installed successfully.
echo --------------------------------------

echo ""
echo ""

if [$systemType == 'new-install']
then
    ##### Install and/or Setup Cache #####
    echo --------------------------------------
    echo /// Install and/or Setup Cache
    echo --------------------------------------
    echo 'Cache is already installed - as this is an existing production VistA system'
    echo See the section \"Installing Cache\" in the file \"README-ahd.md\" for information on installing Cache if needed.
    echo ""
    echo /// Configuring Cache
    echo ""
    echo "Setting adding user 'vista' to the cacheuser group"
    echo ""

    sudo usermod -a -G cacheusr vista

    echo ""
    echo ""
fi


echo Setup CacheServer group
# add group to use for cache admins to start and stop
sudo groupadd cacheserver

echo Setting up Cache Variables
echo ""

vistaServerRoot="/srv"
echo vistaServerRoot = $vistaServerRoot
vistaFilesDir="/srv/mgr"
echo vistaFilesLocation = $vistaFilesDir
vistaDbNamespace="GOLD"
echo vistaDbNamespace = $vistaDbNamespace
vistaDatFile="CACHE.DAT"
echo vistaDatFile = $vistaDatFile
echo DAT File Full Location = $vistaFilesDir/$vistaDbNamespace/$vistaDatFile

echo ""
echo ""

# --------------------------------------
# Is this a new or existing server?
# If a new VistA server, execute the following block of code.
# If the VistA server is already in existance, skip this section.
# --------------------------------------
# Lines 212 through 252 of setup.sh
# --------------------------------------

if [ $systemType == 'new-install' ]
then
    # New-install, so install Cache
    # --------------------------------------
    echo ""
    echo ""
    echo This is a new VistA System, so install Cache
    echo ""

    # 1. install cache
    # 2. stop cahce
    # 3. copy config and database (.DAT)
    # 4. configure users
    # 5. start cache
    
    # 1. install cache
    # --------------------------------------
    echo "Install Cache"
    echo --------------------------------------



    # --------------------------------------
    # 2. stop cahce
    # --------------------------------------
    echo "Stop Cache"
    echo --------------------------------------
    sudo ccontrol stop cache quietly


    # --------------------------------------
    # 3. copy config and database (.DAT)
    # --------------------------------------
    echo "Configure Cache"
    echo --------------------------------------

    echo "Copying cache.cpf configuration file..."
    sudo cp $cacheInstallerPath/cache.cpf $cacheInstallTargetPath/  


    # --------------------------------------
    # 4. configure users
    # --------------------------------------
    echo "Configure Cache Users"
    echo --------------------------------------



    # --------------------------------------
    # 5. setup cache permissions
    # --------------------------------------
    echo "Setup Cache Permissions"
    echo --------------------------------------


    # --------------------------------------


    # --------------------------------------
    # 6. start cache
    # --------------------------------------
    echo "Start up Cache"
    echo --------------------------------------
    sudo ccontrol start cache

    # --------------------------------------
fi

echo --------------------------------------

echo ""
echo ""

echo --------------------------------------
echo /// Enable Cache\'s os Authentication and %Service_CallIn required by EWD.js
echo --------------------------------------


# line 254 in setup.sh
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

echo --------------------------------------

echo ""
echo ""

echo --------------------------------------
echo /// Install the KIDS build - VEFB_1_2
echo --------------------------------------
sudo cp ~/RAPTOR/OtherComponents/VistAConfig/VEFB_1_2.KID /srv/mgr/
# change to the provision folder
cd ~/RAPTOR/provision/
# make 'install-vefb.rb' an executable unix file
dos2unix install-vefb.rb
# chmod 'install-vefb.rb' to make it executable
sudo chmod u+x install-vefb.ruby
sudo ./install-vefb.rb

sudo cp ~/RAPTOR/OtherComponents/EWDJSvistalayer/mumps/vefbrc.ro /srv/mgr/

# FROM ORIGINAL INSTALL - DONT KNOW IF WE NEED
# --------------------------------------------
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

echo --------------------------------------

echo ""
echo ""

echo --------------------------------------
echo /// Configure Apache and Setup Site
echo --------------------------------------

##### Install Drush #####
cd ~
wget -nc --progress=bar:force http://files.drush.org/drush.phar

php drush.phar core-status

# Rename to `drush` instead of `php drush.phar`. Destination can be anywhere on $PATH. 
sudo chmod +x drush.phar
sudo mv drush.phar /usr/local/bin/drush

# Enrich the bash startup file with completion and aliases.
/usr/local/bin/drush -y init

##### Get Drupal 7 #####
echo /// Getting Drupal 7
cd ~
wget -nc --progress=bar:force http://ftp.drupal.org/files/projects/drupal-7.41.tar.gz
tar xzf drupal*
cd drupal*
sudo mkdir /var/www/html/RSite500
sudo rsync -avz . /var/www/html/RSite500
sudo mkdir /var/www/html/RSite500/sites/default/files

##### RAPTOR Application #####
echo /// RAPTOR Application
echo ""
echo Copy RAPTOR modules and themes to the drupal installation
sudo cp -R ~/RAPTOR/modules/* /var/www/html/RSite500/sites/all/modules

sudo cp -R ~/RAPTOR/themes/* /var/www/html/RSite500/sites/all/themes/

# Create tmp Folder
sudo mkdir /var/www/html/RSite500/sites/default/files/tmp

# Configure RSite500 to use the RAPTOR500 database
sudo cp /var/www/html/RSite500/sites/default/settings.php /var/www/html/RSite500/sites/default/settings-original.php 
sudo cp ~/RAPTOR/provision/settings500.php /var/www/html/RSite500/sites/default/settings.php
sudo chmod 664 /var/www/html/RSite500/sites/default/settings.php

# set permissions so $myusername has access to write
sudo chown -R $myusername /var/www/html/RSite500

# remove the $ from the end of this file which causes drush to fail
sudo sed -i '$ d' ~/.drush/drushrc.php

# enable RAPTOR Modules
#cd /var/www/html/RSite500/sites/all/modules/
#/usr/local/bin/drush -y en raptor_contraindications raptor_graph raptor_workflow raptor_datalayer raptor_imageviewing raptor_ewdvista raptor_mdwsvista simplerulesengine_core raptor_floatingdialog raptor_protocollib simplerulesengine_demo raptor_formulas raptor_reports simplerulesengine_ui raptor_glue raptor_scheduling

# automatically download the front-end libraries used by Omega
cd /var/www/html/RSite500/sites/all/themes/omega/omega
sudo chown -R root:cacheusr /var/www/html/RSite500/sites/all/themes/omega/omega
sudo /usr/local/bin/drush -y make libraries.make --no-core --contrib-destination=.
sudo chmod a+rx /var/www/html/RSite500/sites/all/themes/omega/omega/libraries
sudo chown -R apache /var/www/html/RSite500/sites/all/themes/omega/omega

# I'm sure ownership is borked from all the sudo commands...
sudo chown -R apache:apache /var/www


echo --------------------------------------

echo ""
echo ""

echo --------------------------------------
echo /// Install EWD.js and Federator
echo --------------------------------------
sudo mkdir /var/log/raptor
sudo touch /var/log/raptor/federatorCPM.log
sudo touch /var/log/raptor/ewdjs.console.log
sudo chown -R root:cacheusr /var/log/raptor

cd ~/RAPTOR/OtherComponents/EWDJSvistalayer
sudo cp -R ewdjs /opt/
sudo chown -R root:cacheusr /opt/ewdjs

cd ~/RAPTOR/provision
dos2unix install-ewd.rb
sudo chmod u+x install-ewd.rb
sudo ./install-ewd.rb

cd /opt/ewdjs
sudo npm install -g inherits@2.0.0
sudo npm install globalsjs@0.31.0

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

echo --------------------------------------

echo ""
echo ""

echo --------------------------------------
echo /// Finish up ...
echo --------------------------------------
echo 

echo ""
echo "This needs to be revised and rewritten"
echo ""

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