#!/usr/bin/ruby
# If you don't know the code, don't mess around below -BCIV
# Purpose: programatically test the connection to a VistA Instance  
# Written by: Will BC Collins IV {a.k.a., 'BCIV' follow me on GitHub ~ I'm super cool.}
# Email: william.collins@va.gov

require 'pty'
require 'expect'
require 'json'

file = File.read('proof.config.json')
config=JSON.parse(file)

fnames = [] # "csession cache -UVISTA '^ZU'"
PTY.spawn(config['command']) do |r_f,w_f,pid|
   w_f.sync = true

   $expect_verbose = true

   #r_f.expect(/The authenticity of host/) do
   #  w_f.print "y\n"
   #end

   r_f.expect(/^ACCESS CODE: /) do
     w_f.print config['access_code']+"\r"
   end

   r_f.expect(/^VERIFY CODE: /) do
     w_f.print config['verify_code']+"\r"
   end

   r_f.expect(/^Select TERMINAL TYPE NAME: /) do
     w_f.print "C-VT100\r"
   end

   r_f.expect(/Option: /) do
     print "\n-----------------------\n"
     print "Successfully logged in.\n"
     print "-----------------------\n"
     w_f.print "\r\r\r"
   end

   begin
     w_f.print "quit\n"
   rescue
   end
end
