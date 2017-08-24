# Changes to the Original Installation Script

This file shows all modifications that were required to get the installation script to execute successfully, and to make the application function.

* 2017-08-23: Switched the setup.sh file to install Nodejs 6 instead of Nodejs 5
* 2017-08-23: Added new cache.node module EWD node_modules folder to replace old one that won't work with Node 6. Changed the http port numbers that EWD runs on from 8081/8082 to 8080/8081 because we were getting a port conflict. Changed Vagrantfile AWS to use active ami. Old one was kaput.
