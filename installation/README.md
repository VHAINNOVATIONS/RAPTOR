RAPTOR Production Installation Instructions
===========================================

This guide provides production installation instructions for the RAPTOR project.

There are two portions to this installation guide.

1) Application Server RAPTOR and EWD Federator Installation
2) VistA Server EWD.js Installation

Prerequisites
=============
VistA server and target RAPTOR Application server must be running VA approved version of RedHat Enterprise Linux (RHEL).

Obtaining Software
==================
The RAPTOR repository should be copied to both the VistA and RAPTOR Application servers.

The software can be obtained by either downloading a zipped copy of the repository by clicking this link:

```
https://github.com/VHAINNOVATIONS/RAPTOR/archive/automate.zip
```

or by cloning the repository by using the git clone command:

```
git clone https://github.com/VHAINNOVATIONS/RAPTOR.git
```

VistA Server EWD Installation
=============================
1. Use the ssh command or set up a connection through Attachmate Reflection and connect to the Linux server that hosts the VistA instance(s) RAPTOR will communicate with
2. Use the steps, 'Obtaining Software', (above) to obtain the RAPTOR code base
    *If you have a zipped copy run the following command to uncompress the archive:
    ```
    unzip RAPTOR-automate.zip
    ```
3. Run the following commands to install EWD:

```
cd RAPTOR-automate/installation
sudo chmod u+x install-ewd-on-VistA-server.sh
sudo ./install-ewd-on-VistA-server.sh
```

4. It is necessary to obtain the database interface that is specific to your version of Intersystems Caché for use by EWD.  
    Change the source path of the Caché installation, if it differs from the command below:
    ```
    sudo cp /srv/bin/cache0100.node /opt/ewdjs/node_modules/cache.node
    ```
5. Install the EWD.js KIDS build into your VistA namespace.
   Either run the commands manually or Change ACCESS_CODE, VERIFY_CODE, Instance, and namespace as needed to run from Linux shell:
    ```
    csession CACHE -UVISTA "^ZU" <<EOI
    ACCESS_CODE
    VERIFY_CODE
    ^^load a distribution
    /srv/mgr/VEFB_1_2.KID
    yes
    ^^install package
    VEFB 1.2
    no
    no

    ^
    ^
    h
    EOI
    ```
6. Modify the registerEWDFederator.js script located at /opt/ewdjs
  Lines 5-8 must be modified to identify the correct path to the Caché installation, username, password, and namespace:

    ```
    path: '/srv/mgr',
    username: '_SYSTEM',
    password: 'innovate',
    namespace: 'VISTA'
    ```
  Line 21 must be modified to contain the secretKey that will be used in production.
    
    ```
    "secretKey": "$keepSecret!",
    ```
    After altering the registerEWDFederator.js script, save changes
7. Run the registerEWDFederator.js script:
    ```
    node registerEWDFederator.js
    ```
8. Modify the ewdStart-raptor.js script located at /opt/ewdjs
    Lines 26-29 must be modified to identify the correct path to the Caché installation, username, password, and namespace:
    ```
      path:"/srv/mgr",
      username: "_SYSTEM",
      password: "innovate",
      namespace: "VISTA"
    ```
    Line 33 must be modified to contain the password that will be used for the EWD management interface:
    ```
      password: 'innovate'
    ```
9. To start EWD use the following command:
    ```
    sudo nohup node ewdStart-raptor > /var/log/raptor/ewdjsCPM.log 2>&1 &
    ```

RAPTOR Application Installation
===============================
1. Use the ssh command or set up a connection through Attachmate Reflection and connect to the target RAPTOR application server

2. Use the steps, 'Obtaining Software', (above) to obtain the RAPTOR code base
    *If you have a zipped copy run the following command to uncompress the archive:

```
unzip RAPTOR-automate.zip
```

3. run the following commands to install RAPTOR:

```
cd RAPTOR-automate/installation
sudo chmod u+x setup.production.sh
sudo ./setup.production.sh
```

4. Use the scp command to copy the 'cache0100.node' file from the VistA server to the /opt/ewdjs/node_modules/ folder and rename it as: cache.node

5. Modify the startFederator.js script located at /opt/ewdjs
    Lines 10-13 must be modified to identify the correct path to the Caché installation, username, password, and namespace:

```
path:"/srv/mgr",
username: "_SYSTEM",
password: "innovate",
namespace: "VISTA"
```

    Lines 18-24 must be modified to identify the host (VistA server IP), and secretKey that was set in Step 5. (line 21) of the VistA EWD Installation:

```
host: '127.0.0.1',  // if federator installed on same physical machine as EWD.js / VistA
//EWDVISTA
port: 8082,
ssl: false,
ewdjs: true,
accessId: 'ewdfederator',  // change as needed
secretKey: '$keepSecret!'  // change as needed
```

6. To start the Federator use the following command:
    ```
    sudo nohup node startFederator > /var/log/raptor/federatorCPM.log 2>&1 &
    ```
7. Check the Federator installation by opening a browser that points to the following:
    EWD Federator: http://<server ip>:8081/RaptorEwdVista/raptor/
8. By default the installed Raptor instance is named RSite500.  This is a generic identifier.
   A. The intended way to present each unique site is to copy this site to reflect the correct site ID as follows (where the VA Site ID is 777):
   ```
   cd /var/www/html/
   sudo cp RSite500 RSite777
   ```
   B. The database should also have a name relevant to the correct VA Site ID and DATABASE_PASS:
   ```
    export DATABASE_PASS='raptor1!'
    mysql -u root -p"$DATABASE_PASS" -h localhost -e "create database raptor777;"
    mysql -u root -p"$DATABASE_PASS" -h localhost raptor777 < /vagrant/provision/drupal.sql
    # add RAPTOR database user and assign access
    mysql -u root -p"$DATABASE_PASS" -h localhost -e "create user raptoruser@localhost identified by '$DATABASE_PASS';"
    mysql -u root -p"$DATABASE_PASS" -h localhost -e "GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,INDEX,ALTER,CREATE TEMPORARY TABLES,LOCK TABLES ON raptor777.* TO raptoruser@localhost;"
    mysql -u root -p"$DATABASE_PASS" -h localhost -e "FLUSH PRIVILEGES;"
   ```
   C. The Raptor application configuration file must be updated to reflect the database information:
      Modify lines 220-224 of /var/www/html/Rsite777/sites/default/settings.php to reflect the correct database information:
      ```
      'database' => 'raptor777',
      'username' => 'root',
      'password' => 'raptor1!',
      'host' => 'localhost',
      'port' => '',
      'driver' => 'mysql',
      'prefix' => '',      
      ```
9. Check the RAPTOR installation by opening a browser that points to the following:
    RAPTOR: http://192.168.33.11/RSite777/

Additional Documentation can be found here:
===========================================

https://github.com/VHAINNOVATIONS/RAPTOR/tree/automate/SharedDocs

