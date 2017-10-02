#!/bin/bash
# __     ___   _    _      _____ _____ _     
# \ \   / / | | |  / \    |  ___|_   _| |    
#  \ \ / /| |_| | / _ \   | |_    | | | |    
#   \ V / |  _  |/ ___ \  |  _|   | | | |___ 
#    \_/  |_| |_/_/   \_\ |_|     |_| |_____|
#       VHA Future Technology Laboratory
#               https://vaftl.us
# -------------------------------------------
# Adge Denkers
# adge@vaftl.us
# Future Technology Laboratory
# U.S. Department of Veterans Affairs
# project: RAPTOR
# file name: setup-on-vista-ahd.sh
# date: 2017-09-28
# -------------------------------------------

echo 'starting: RAPTOR (setup-on-vista-ahd.sh)'

#
#
# Come Back to sections labeled: 
# ##### DID NOT EXECUTE YET #####
#
#


##### Setup Variables #####
echo -------------------------------------------
echo *** Setup Variables
echo -------------------------------------------
myusername=$USER
echo myusername = $myusername
vistaFilesDir="/srv/mgr"
echo vistaFilesLocation = $vistaFilesDir
vistaDbNamespace="GOLD"
echo vistaDbNamespace = $vistaDbNamespace
vistaDatFile="CACHE.DAT"
echo vistaDatFile = $vistaDatFile
echo DAT File Full Location = $vistaFilesDir/$vistaDbNamespace/$vistaDatFile

echo -------------------------------------------




# -------------------------------------------
echo Install pre-requisite software...
echo -------------------------------------------
sudo yum -y install git wget unzip zip ruby gcc gcc-c++ nano make dos2unix 
##### OK #####
echo "Installation of the above components complete, without any issue."

sudo gem install -q json -v '1.8.3'
######### RESOLVED - ERROR ##########
# Building native extensions.  This could take a while...
# ERROR:  Error installing json:
# 	ERROR: Failed to build gem native extension.
#
#    /usr/bin/ruby extconf.rb
# mkmf.rb can't find header files for ruby at /usr/share/include/ruby.h
#
#
# Gem files will remain installed in /usr/local/share/gems/gems/json-1.8.3 for inspection.
# Results logged to /usr/local/share/gems/gems/json-1.8.3/ext/json/ext/generator/gem_make.out
##########################
 
##### RESOLVED - ERROR - NOTES ######
# RESOLUTION
# grep -B1 -i optional /etc/yum.repos.d/*
# # The ID of the repository is what is displayed in brackets []
# # Copy the repository id for the "optional" repository (not "source-optional")
# sudo yum-config-manager --enable rhui-REGION-rhel-server-optional
# sudo yum -y install ruby-devel
# sudo gem install -q json -v '1.8.3'
# ##### OK #####
# # Install sucseeded
##########################

sudo gem install -q expect -v '0.0.13'
##### OK #####


##### Configure Linux #####
##### DID NOT EXECUTE YET #####
#echo configuring ipv4 firewall
#echo -----------------------
#sudo service iptables stop
#sudo cp /vagrant/provision/iptables /etc/sysconfig/
#sudo service iptables start 
##########################

##### Install Cache #####
# Cache is already installed - as this is an existing production VistA system
##########################


##### Stop Cache #####
echo -------------------------------------------
echo Stopping the Cache server.
echo -------------------------------------------
sudo ccontrol stop cache quietly
echo -------------------------------------------

##### Set Database Permissions Correctly #####
##############################################
# This needs to be reviewed at install-time
# to ensure permissions are correct on the
# production VistA database.
##############################################
echo -------------------------------------------
echo Setting permissions on the VistA database.
echo -------------------------------------------
sudo chown -R root:cacheusr /srv
sudo chmod g+wx /srv/bin
sudo chmod 775 /srv/mgr/GOLD 
sudo chmod 660 /srv/mgr/GOLD/CACHE.DAT
sudo chown -R root:cacheusr /srv/mgr/GOLD 
echo -------------------------------------------

#!/bin/bash
# __     ___   _    _      _____ _____ _     
# \ \   / / | | |  / \    |  ___|_   _| |    
#  \ \ / /| |_| | / _ \   | |_    | | | |    
#   \ V / |  _  |/ ___ \  |  _|   | | | |___ 
#    \_/  |_| |_/_/   \_\ |_|     |_| |_____|
#       VHA Future Technology Laboratory
#               https://vaftl.us
# -------------------------------------------
# Adge Denkers
# adge@vaftl.us
# Future Technology Laboratory
# U.S. Department of Veterans Affairs
# project: \
# file name: 
# date: 
# -------------------------------------------

echo  __     ___   _    _      _____ _____ _     
echo  \ \   / / | | |  / \    |  ___|_   _| |    
echo   \ \ / /| |_| | / _ \   | |_    | | | |    
echo    \ V / |  _  |/ ___ \  |  _|   | | | |___ 
echo     \_/  |_| |_/_/   \_\ |_|     |_| |_____|
echo       VHA Future Technology Laboratory
echo               https://vaftl.us
echo  -----------------------------------------------

echo ''
echo 'starting: \ ()'

# -------------------------------------------



