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

##### Setup Variables #####
echo --------------------------------------
echo *** Setup Variables
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

myusername=$USER
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
echo *** Install pre-requisite software...
echo --------------------------------------

##### Enable the RedHat Optional Repository #####
echo *** Enable the \'optional\' repository for RHEL
echo ""
sudo yum-config-manager --enable rhui-REGION-rhel-server-optional
echo ""
echo ""
echo See the section \"Enabling the RedHat Optional Repository\" in the file \"README-ahd.md\" for information on enabling the RedHat Optional Repository if this step fails.
echo ""

echo ""
##### Install Pre-requisite Software Packages #####
echo *** Install Pre-requisite Software Packages
sudo yum -y install git wget unzip zip ruby gcc gcc-c++ nano make dos2unix ruby-devel 

echo ""
##### Install Ruby Gems #####
echo *** Install Ruby Gems 
sudo gem install -q json -v '1.8.3'
sudo gem install -q expect -v '0.0.13'

echo ""
echo Pre-requisite software installed successfully.
echo --------------------------------------

echo ""
echo ""

if [$systemType == 'new-install']
then
    ##### Install and/or Setup Cache #####
    echo --------------------------------------
    echo *** Install and/or Setup Cache
    echo --------------------------------------
    echo 'Cache is already installed - as this is an existing production VistA system'
    echo See the section \"Installing Cache\" in the file \"README-ahd.md\" for information on installing Cache if needed.
    echo ""
    echo *** Configuring Cache
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
echo *** Enable Cache\'s os Authentication and %Service_CallIn required by EWD.js
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
echo *** Install the KIDS build - VEFB_1_2
echo --------------------------------------



echo --------------------------------------

echo ""
echo ""

echo --------------------------------------
echo *** Install EWD.js and Federator
echo --------------------------------------



echo --------------------------------------

echo ""
echo ""

echo --------------------------------------
echo *** Finish up ...
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

