# Changes to the Original Installation Script

This file shows all modifications that were required to get the installation script to execute successfully, and to make the application function.

* 2017-08-23: Switched the setup.sh file to install Nodejs 6 instead of Nodejs 5
* 2017-08-23: Added new cache.node module EWD node_modules folder to replace old one that won't work with Node 6. Changed the http port numbers that EWD runs on from 8081/8082 to 8080/8081 because we were getting a port conflict. Changed Vagrantfile AWS to use active ami. Old one was kaput.
* 2017-08-29: Project is ready to build. RAPTOR does not authenticate VistA ids, but this appears to be due to RAPTOR code, not the Vagrant environment or the EWD product. Will need developer troubleshooting to identify where failure is occurring in the codebase. When trying to browse the EWDRaptor 
              service at http://192.168.33.11:8081/RaptorEwdVista/raptor/ you get a "failed authentication(1)" error, which I traced to '/OtherComponents/EWDJSvistalayer/ewdjs/node_modules/VistALib.js.' Rob Tweed, author of EWD and I believe this to be the error that is short-circuiting the RAPTOR VistA login.
              Debugging at the Javascript level will likely be necessary to determine the true source of the error. 