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
Troubleshooting
---------------
If you see the following error messages in the Vagrant output:
```
==> RAPTOR: + /usr/local/bin/drush -y en raptor_contraindications raptor_graph raptor_workflow raptor_datalayer raptor_i
mageviewing raptor_ewdvista raptor_mdwsvista simplerulesengine_core raptor_floatingdialog raptor_protocollib simplerules
engine_demo raptor_formulas raptor_reports simplerulesengine_ui raptor_glue raptor_scheduling
==> RAPTOR: Drush command terminated abnormally due to an unrecoverable error.       [error]
==> RAPTOR: Error: syntax error, unexpected end of file, expecting variable
==> RAPTOR: (T_VARIABLE) or '$' in /root/.drush/drushrc.php, line 292
==> RAPTOR: + cd /var/www/html/RSite500/sites/all/themes/omega/omega
==> RAPTOR: + sudo chown -R vagrant /var/www/html/RSite500/sites/all/themes/omega/omega
==> RAPTOR: + /usr/local/bin/drush -y make libraries.make --no-core --contrib-destination=.
==> RAPTOR: Drush command terminated abnormally due to an unrecoverable error.       [error]
==> RAPTOR: Error: syntax error, unexpected end of file, expecting variable
==> RAPTOR: (T_VARIABLE) or '$' in /root/.drush/drushrc.php, line 292
==> RAPTOR: + sudo chmod a+rx /var/www/html/RSite500/sites/all/themes/omega/omega/libraries
==> RAPTOR: chmod:
==> RAPTOR: cannot access `/var/www/html/RSite500/sites/all/themes/omega/omega/libraries'
==> RAPTOR: : No such file or directory
==> RAPTOR: + sudo chown -R apache /var/www/html/RSite500/sites/all/themes/omega/omega
==> RAPTOR: + cd /var/www/html/RSite500/sites/all/themes/
==> RAPTOR: + /usr/local/bin/drush -y -l http://localhost/RSite500/ pm-enable raptor_omega
==> RAPTOR: Drush command terminated abnormally due to an unrecoverable error.       [error]
==> RAPTOR: Error: syntax error, unexpected end of file, expecting variable
==> RAPTOR: (T_VARIABLE) or '$' in /root/.drush/drushrc.php, line 292
```

Run the following commands:
```
vagrant ssh

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
/usr/local/bin/drush -y -l http://localhost/RSite500/ pm-enable raptor_omega
# drush -y -l http://localhost/RSite500/ vset theme_default raptor_omega
# drush -y -l http://localhost/RSite500/ omega-export raptor_omega

# I'm sure ownership is borked from all the sudo commands...
sudo chown -R apache:apache /var/www
```
