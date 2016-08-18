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

print "BC's EWD Installer...\r"

#file = File.read('config.local.json')
#config=JSON.parse(file)

fnames = [] # "csession cache -UVISTA '^ZU'"
#PTY.spawn(config['command']) do |r_f,w_f,pid|
PTY.spawn('sudo su') do |r_f,w_f,pid|
   w_f.sync = true
   $expect_verbose = true

   r_f.expect(/\]\# /) do
     w_f.print "cd /opt/ewdjs\r"
   end

   r_f.expect(/ewdjs\]\# /) do
     w_f.print 'sudo npm install ewdjs@0.103.0'+"\r"
   end

   r_f.expect(/Install EWD.js to directory path \(\/opt\/ewdjs\): /) do
     w_f.print "\r"
   end

   r_f.expect(/Enter Y\/N: /) do
     w_f.print "Y\r"
   end

   r_f.expect(/\]\# /) do
     #w_f.print "sudo npm install -g globalsjs@0.31.0\r"
     w_f.print "sudo npm install ewd-federator\r"
   end

   r_f.expect(/\]\# /) do
     w_f.print "sudo npm install -g inherits@2.0.0\r"
   end


#   r_f.expect(/\]\# /) do
#   end

   begin
     w_f.print "quit\n"
     print "fin\n";
   rescue
   end
end
