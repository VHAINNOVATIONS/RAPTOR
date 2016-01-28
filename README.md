RAPTOR
======

Radiology Protocol Tool Recorder

RAPTOR is an automated, electronic tool for capturing data that is needed by radiologists to optimize advanced medical imaging protocols including CT, MRI and nuclear medicine. RAPTOR helps to optimize advanced medical imaging protocols at the Veterans Health Administration (VHA) by automating an existing paper-based, error-prone manual process that can take weeks to complete. RAPTOR improves upon the existing system by capturing medical device data automaticall from Veterans Health Information Systems and Technology Architecture (VistA). 

Dependencies
------------
- Apache2/IIS
- MySQL
- PHP5
- Drupal 7
- Node.js
- EWD.js
- Intersystems Cache'
- VistA

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

Credentials
-----------
Drupal 7
http://<ip address of provisioned system>/
username: admin
password: raptor1!


