#!/usr/bin/ruby
# If you don't know the code, don't mess around below -BCIV
# Purpose: programatically install vista without parameters file which fails under
#          Vagrant using Windows 10 as the Host Operating System. 
# Written by: Will BC Collins IV {a.k.a., 'BCIV' follow me on GitHub ~ I'm super cool.}
# Email: william.collins@va.gov

require 'rubygems'
require 'pty'
require 'expect'
require 'json'

print "BC's Cache Installer... \r"

file = File.read('config.local.json')
config=JSON.parse(file)

fnames = [] # "csession cache -UVISTA '^ZU'"
#PTY.spawn(config['command']) do |r_f,w_f,pid|
PTY.spawn('sudo su') do |r_f,w_f,pid|
   w_f.sync = true
   $expect_verbose = true

   #r_f.expect(/The authenticity of host/) do
   #  w_f.print "y\n"
   #end

   r_f.expect(/\]\# /) do
     w_f.print 'cd '+ config['cacheInstallTargetPath']+"/tmp \r"
   end

   r_f.expect(/tmp\]\# /) do
     w_f.print 'sudo ./cinstall'+"\r"
   end

   r_f.expect(/Enter instance name <CACHE>: /) do
     w_f.print "\r"
   end

   r_f.expect(/Directory: /) do
     w_f.print config['cacheInstallTargetPath'] + "\r"
   end

   r_f.expect(/Setup type <1>?/) do
     w_f.print "1\r"
   end

   r_f.expect(/Do you want to install Cache Unicode support <No>?/) do
     w_f.print "\r"
   end

   r_f.expect(/Initial Security settings <1>?/) do
     w_f.print "2\r"
   end

   r_f.expect(/What user should be the owner of this instance?/) do
     w_f.print config['instanceOwner'] + "\r"
   end

  r_f.expect(/Please enter the common password for these accounts:/) do
    w_f.print config['commonPassword'] + "\r"
  end
  
  r_f.expect(/Re-enter the password to confirm it:/) do
    w_f.print config['commonPassword'] + "\r"
  end

  r_f.expect(/this instance?/) do
    w_f.print config['cacheGroup'] + "\r"
  end

  #r_f.expect(/Do you want to configure the CSP Gateway to use an existing web server <No>?/) do
  #  w_f.print "\r"
  #end

  #r_f.expect(/Do you want to enter a license key <No>?/) do
  #  w_f.print "yes\r"
  #end
  #
  #r_f.expect(/License key file:/) do
  #  w_f.print config['cacheInstallerPath'] + "/cache.key\r"
  #end 

  r_f.expect(/Do you want to proceed with the installation <Yes>?/) do
    w_f.print "yes\n"
    # wait for the install to complete...
    print "\n\nplease wait for installation to complete...\n"
    sleep(60*2)
  end
  
   begin
     #w_f.print "quit\n"
     print "cache installation complete\n";
   rescue
   end
end
