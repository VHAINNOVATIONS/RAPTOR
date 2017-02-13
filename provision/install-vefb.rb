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

print "VEFB KIDS Installer...\r"

#file = File.read('config.local.json')
#config=JSON.parse(file)

fnames = [] # "csession cache -UVISTA '^ZU'"
#PTY.spawn(config['command']) do |r_f,w_f,pid|
PTY.spawn('csession cache -UVISTA '^ZU'') do |r_f,w_f,pid|
   w_f.sync = true
   $expect_verbose = true

#csession CACHE -UVISTA "^ZU" <<EOI
#cprs1234
#cprs4321$
#^^load a distribution
#/srv/mgr/VEFB_1_2.KID
#yes
#^^install package
#VEFB 1.2
#no
#no
#
#^
#^
#h
#EOI

   r_f.expect(/ACCESS CODE: /) do
     w_f.print "cprs1234\r"
   end

   r_f.expect(/VERIFY CODE: /) do
     w_f.print "cprs4321$\r"
   end

   r_f.expect(/^Select TERMINAL TYPE NAME: /) do
     w_f.print "C-VT100\r"
   end

   r_f.expect(/Option: /) do
     w_f.print "^^LOAD A DISTRIBUTION\r"
   end

   r_f.expect(/Enter a Host File: /) do
     w_f.print "/srv/mgr/VEFB_1_2.KID\r"
   end
   
   r_f.expect(/OK to continue with Load? NO\/\//) do
     w_f.print "YES\r"
   end

   r_f.expect(/Want to Continue with Load? YES\/\//) do
     w_f.print "YES\r"
   end

   r_f.expect(/Option:/) do
     w_f.print "^^install package\r"
   end

   r_f.expect(/Select INSTALL NAME:/) do
     w_f.print "VEFB 1.2\r"
   end

   r_f.expect(/Want KIDS to INHIBIT LOGONs during the install? NO\/\//) do
     w_f.print "\r"
   end

   r_f.expect(/Want to DISABLE Scheduled Options, Menu Options, and Protocols? NO\/\//) do
     w_f.print "\r"
   end

   r_f.expect(/DEVICE: HOME\/\//) do
     w_f.print "\r"
   end

   r_f.expect(/Option:/) do
     w_f.print "\r\r\r"
   end

   begin
     w_f.print "quit\n"
     print "fin\n";
   rescue
   end
end