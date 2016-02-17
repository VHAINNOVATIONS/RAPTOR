RAPTOR
======

Radiology Protocol Tool Recorder

RAPTOR is an automated, electronic tool for capturing data that is needed by radiologists to optimize advanced medical imaging protocols including CT, MRI and nuclear medicine. RAPTOR helps to optimize advanced medical imaging protocols at the Veterans Health Administration (VHA) by automating an existing paper-based, error-prone manual process that can take weeks to complete. RAPTOR improves upon the existing system by capturing medical device data automaticall from Veterans Health Information Systems and Technology Architecture (VistA). 

Dependencies
------------
- Apache2/IIS
- MySQL
- PHP5
    - php-soap
    - php-dom
    - php-xml
    - mbstring
- Drupal 7
    - omega theme
        - selectivizr.min.js
        - html5shiv.min.js
        - html5shiv-printshiv.min.js
        - respond.min.js
        - raptor_omega theme
- Node.js
- EWD.js
- Intersystems Cache'
- VistA
    - Raptor specific KIDs builds
    - EWD integration

Prerequisites
-------------
- Install Vagrant (see: https://www.vagrantup.com/docs/installation/ )
- Install VirtualBox (see: https://www.virtualbox.org/wiki/Downloads )
- Install Git (see: operating system specific instructions ) 

Installation
------------
This automated installation process will create a RAPTOR system on a Linux-based virtual machine.

- Open your terminal application such as a shell under Linux, Command Prompt under Windows, or Terminal.app on Mac.
- Clone this repository: git clone https://github.com/VHAINNOVATIONS/RAPTOR.git
- Use the cd command to go to the root folder of the repository
- Type the following command to build RAPTOR:
```
vagrant up
```
To provision in AWS adjust your environment variables or modify the 'aws' section of the Vagrantfile and execute:
```
vagrant up --provider=aws
```
Manual Steps
------------
Ewd with Caché uses the %Service_callin interface that must be enabled.  
The Callin service lets EWD execute and evaluate Cache ObjectScript commands 
and expressions from within the C program that EWD has as it's binding to the
database.

At this time, the %Service_Callin is not automatically enabled within Caché.
It is necessary, after provisioning, to go the the CSP (link below) login 
as: vagrant password: innovate

- click on System Administration -> Security -> Services
- click on %Service_Callin 
- check 'Service Enabled'
- click 'save' button.
- Then, to start EWD type:
```
vagrant ssh
cd /opt/ewdjs
sudo nohup node ewdStart-raptor > /var/log/raptor/ewdjsCPM.log 2>&1 &
```
Now EWD is running and you should try to log into the RAPTOR application
using a user such as:

radiologist12/12radiologist 


Credentials
-----------
Raptor Site (default site is 500)
http://192.168.33.11/RSite500/
username: admin
password: raptor1!

Configuration
-------------
The following values may be changed to suit your environment:

If you have a vista system with a very long workload listing, this value can be adjusted to make the worklist more managable:
```
OLDEST_WORKLIST_TICKET_ID = 30000
```

VistA Terminal Session
----------------------
```
csession cache -U VISTA "^ZU"
```
username: vagrant 
password: innovate 

Troubleshooting
---------------
Cache
-----
ERROR MESSAGE:

Crash on D ^ZU

> VistA logon (the ZU program) displays
>         "** TROUBLE ** - ** CALL IRM NOW! **"
> if the number of available jobs drops below 3. For those of us with
> small Cache licenses, this is a permanent condition, so you may decide
> to edit the ZU program to change the threshold.

Caused by not having a valid license key with enough seats.

ref: https://www.mail-archive.com/hardhats-members@lists.sourceforge.net/msg01755.html 

EWD Federator - http://192.168.33.11:8081/RaptorEwdVista/raptor/
-------------
ERROR MESSAGE: 

{"code":"RESTError","message":"ewdfederator is not authorised for Web Service access"}
... looking into this...