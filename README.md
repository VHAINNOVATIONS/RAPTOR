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

Installation for Production
---------------------------
https://github.com/VHAINNOVATIONS/RAPTOR/blob/automate/installation/README.md

Installation for Demonstration and Development
----------------------------------------------
This automated installation process will create a RAPTOR system on a Linux-based virtual machine.

- Open your terminal application such as a shell under Linux, Command Prompt under Windows, or Terminal.app on Mac.
- Clone this repository: git clone https://github.com/VHAINNOVATIONS/RAPTOR.git
- Use the cd command to go to the root folder of the repository
- Type the following command to build RAPTOR:
```
vagrant up
```
If you change the Unix username that you install RAPTOR from you will need to change your username in the provisioning scripts.

To customize the credentials used for the Caché installation it is necessary to edit the provision/config.local.json file:
```
{
    "cacheInstallerPath": "/vagrant/provision/cache",
    "cacheInstaller": "cache-2014.1.3.775.14809-lnxrhx64.tar.gz",
    "cacheInstallTargetPath": "/srv",
    "instanceOwner": "vagrant",
    "commonPassword": "innovate",
    "cacheGroup": "cacheserver"
}
```
Further in the provision/setup.sh the install-cache.rb script parses this config.local.json file to use values for installation of InterSystem's Caché.

Afterwards, you can build the machine in AWS as follows:
```
vagrant up --provider=aws
```

To connect to roll and scroll VistA to install VEFB KIDS build for RAPTOR
```
vagrant ssh
csession cache
```
at prompt enter credentials with username: vagrant and password: innovate
```
D ^%CD
```
at namespace prompt enter:
```
VISTA
```
Type the following to get a prompt for access/verify code:
```
D ^ZU
```
Enter access/verify code pair: CPRS1234/CPRS4321$
```
Select Systems Manager Menu <TEST ACCOUNT> Option: ^^load a distribution
Enter a Host File: /srv/mgr/VEFB_1_2.KID

KIDS Distribution saved on Sep 28, 2015@08:20:31
Comment: RAPTOR KIDS

This Distribution contains Transport Globals for the following Package(s):
   VEFB 1.2
Distribution OK!

Want to Continue with Load? YES//
Loading Distribution...

   VEFB 1.2
Use INSTALL NAME: VEFB 1.2 to install this Distribution.

```

EWDJS and EWD Federator
-----------------------
EWDJS and FEDERATOR resides within the /opt/ewdjs folder 

To manually start it you can use this command (which executes /opt/ewdjs/startEverything.sh):
```
cd /opt/ewdjs
npm run start 
```
To stop it use this (evoking /opt/ewdjs/killEverything.sh):
```
cd /opt/ewdjs
npm run stop
```

Important EWD Links on RAPTOR Development VM
--------------------------------------------
EWD Monitor: http://192.168.33.11:8082/ewd/ewdMonitor/index.html password: innovate 
EWD: http://192.168.33.11:8082/ewdjs/EWD.js ewdBootstrap3.js 
EWD Federator: http://192.168.33.11:8081/RaptorEwdVista/raptor/
echo password: innovate 

Cache Links on RAPTOR Development VM
------------------------------------
CSP is here: http://192.168.33.11:57772/csp/sys/UtilHome.csp
username: vagrant 
password: innovate

Now EWD is running and you should try to log into the RAPTOR application
using a user such as:

1radiologist/cprs1234$


Credentials
-----------
Raptor Site (default site is 500)
http://192.168.33.11/RSite500/
username: admin
password: raptor1!

- Resident access/verify: 1radiologist/cprs1234$
- Radiologist access/verify: radio1234/Rad12345!
- Scheduler access/verify: cprs1234/cprs4321$

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
username: cprs1234 
password: cprs4321$ 

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

This means that the ewdfederator user has not been set up.

To fix this error see the /opt/ewdjs/RegisterEWDFederator.js script that contains the
credentials for the federator to use.  

```
var ewdGlobals = require('./node_modules/ewdjs/node_modules/globalsjs');
var interface = require('cache');
var db = new interface.Cache();
var ok = db.open({
  path: '/srv/mgr',
  username: '_SYSTEM',
  password: 'innovate',
  namespace: 'VISTA'
});

ewdGlobals.init(db);

var ewd = {
  mumps: ewdGlobals
};

var zewd = new ewd.mumps.GlobalNode('%zewd', []);
zewd._setDocument({
  "EWDLiteServiceAccessId": {
    "ewdfederator": {
      "secretKey": "$keepSecret!",
      "apps": {
        "raptor": true
      }
    }
  }
});

db.close();
```

The startFederator.js script must match these credentials as well.
```
var federator = require('ewd-federator');

var params = {
  //FEDERATOR
  restPort: 8081,
  poolSize: 2,
  traceLevel: 3,
      database: {
        type: 'cache',
        //path:"c:\\InterSystems\\Cache\\Mgr",
        path:"/srv/mgr",
        username: "_SYSTEM",
        password: "innovate",
        namespace: "VISTA"
      },
  server: {

    RaptorEwdVista: {
      host: '127.0.0.1',  // if federator installed on same physical machine as EWD.js / VistA
	  //EWDVISTA
      port: 8082,
      ssl: false,
      ewdjs: true,
      accessId: 'ewdfederator',  // change as needed
      secretKey: '$keepSecret!'  // change as needed
    }
  },

  service: {
    raptor: {
      module: 'raptor',
      service: 'parse',
      contentType: 'application/json'
    }
  }

};

federator.start(params);
```