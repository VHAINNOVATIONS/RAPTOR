#!/usr/bin/perl
# DIFR Custom Setup Utility
# 2015-09-16 BCIV

# args to ask for upon execution

# branding
print "Dynamic Interface For Records (DIFR) Custom Setup\n";
print "Copyright 2015 BCIV through EtherFeat LLC\n";
print "2015-09-16 build 0.1.1\n";

# make sure user is root
my $login = (getpwuid $>);
die "This script must run as root or sudo" if $login ne 'root';

# prompt for root webserver folder
print "\n\n======================\n";
print "=  Web Server Root   =\n";
print "======================\n";
print "Typically a root WebServer folder is below a folder where HTML files are served from.\n";
print "On a Linux system, it is usually /var/www.\n";
print "Enter the root WebServer data folder path on your system?: ";
my $wwwroot= <STDIN>;
chomp $wwwroot;
exit 0 if ($wwwroot eq '');

# prompt for application title
print "\n\n======================\n";
print "= Application Title  =\n";
print "======================\n";
print "Enter the title for your application/website (can have spaces ~ use quotes if you use spaces): ";
my $app_title= <STDIN>;
chomp $app_title;
exit 0 if ($app_title eq '');

# prompt for domain name
print "\n\n======================\n";
print "=     Domain Name    =\n";
print "======================\n";
print "Enter the domain name (for localhost dev only, type 'localhost'): ";
my $domain_name= <STDIN>;
chomp $domain_name;
exit 0 if ($domain_name eq '');

# prompt for application folder name or use domain name
# create path under wwwroot and copy files there
print "\n\n======================\n";
print "= Application Folder =\n";
print "======================\n";
print "Enter a name for your application folder within difr framework.\n";
print "If you leave this blank, the domain name you entered will be used: ";
my $app_name= <STDIN>;
chomp $app_name;
if ($app_name eq ''){
  print "Your domain name, $domain_name, will be used for the application folder name.\n";
  $app_name=$domain_name;
}  

# make sure we aren't accidentally destroying a previous difr instance
if(-d "$wwwroot/difr/$app_name"){
  print "\n\n======================================\n";
  print "= WARNING: Instance Already Exists!! =\n";
  print "======================================\n";    
  print "Folder, $wwwroot/difr/$app_name, already exists.\n";
  print "Answering YES will destroy any previous configuration.\n";
  print "Proceed? : ";
  my $answer=<STDIN>;
  chomp $answer;
  unless($answer eq 'YES'){ die "Installation Aborted.\n"; }
}

# create difr instance folder and copy clean instance  
system("mkdir -p $wwwroot/difr/$app_name");
if(-d "$wwwroot/difr/$app_name"){
  my $cmd="cp -R difr/* $wwwroot/difr/$app_name/";
  system($cmd);
  print "Application difr instance created at: $wwwroot/difr/$app_name\n";
}
else{
  die "Cannot create Application difr instance at: $wwwroot/difr/$app_name\n";
}

# prompt for folder html files are served from
print "\n\n======================\n";
print "=  HTML File Folder  =\n";
print "======================\n";
print "What is the name of the public html folder?  (Typically 'html' or 'public'): ";
my $public= <STDIN>;
chomp $public;
if($public eq ''){
  die "You must supply the name of the public html folder.\n";
}
else{
  # check public folder existence
  if(-d "$wwwroot/$public"){
    print "HTML folder found...\n";
  }
  else{
    die "Cannot verify HTML folder at: $wwwroot/$public";
  }
  $public="$wwwroot/$public";
}

# prompt for sub folder if desired
print "\n\n=======================\n";
print "= Sub Folder for HTML =\n";
print "=======================\n";
print "Optionally, you can install difr in a subfolder of the HTML file folder.\n";
print "If you do not install in a subfolder, difr may overwrite existing files.\n";
print "If you want to install in a subfolder, type the name here or hit enter: ";
my $subfolder=<STDIN>;
chomp $subfolder;
unless($subfolder eq ''){ $public.="/$subfolder"; }

# copy difr public files into $html_folder
system("mkdir -p $public");
if(-d $public){
  my $cmd="cp -R public/* $public/";
  print "$cmd\n";
  system($cmd);
  
  print "Application will be served from: $public\n";
}
else{
  die "Cannot create folder: $public\n";
}

# get root mysql username
print "\n\n========================\n";
print "= MySQL Admin Username =\n";
print "========================\n";
print "Enter MySQL Admin username: ";
my $dbadmin_username=<STDIN>;
chomp $dbadmin_username;
if($dbadmin_username eq ''){ die "You must supply a MySQL Admin Username.  Installation Aborted!\n"; }

# get root mysql password
print "\n\n========================\n";
print "= MySQL Admin Password =\n";
print "========================\n";
print "Enter MySQL Admin password: ";
my $dbadmin_password=<STDIN>;
chomp $dbadmin_password;
if($dbadmin_password eq ''){ die "You must supply a MySQL Admin Password.  Installation Aborted!\n"; }

# get mysql server name
print "\n\n===================\n";
print "= MySQL Server Name =\n";
print "=====================\n";
print "Enter MySQL Server Name (default is 'localhost'): ";
my $dbserver_hostname=<STDIN>;
chomp $dbserver_hostname;
if($dbserver_hostname eq ''){ $dbserver_hostname='localhost'; }

# get mysql database name
print "\n\n===================\n";
print "= MySQL Database Name =\n";
print "=====================\n";
print "Enter MySQL Database Name (default is '$app_name'): ";
my $dbname=<STDIN>;
chomp $dbname;
if($dbname eq ''){ $dbname=$app_name; }

# get db app username
print "\n\n==============================\n";
print "= MySQL App Username to create =\n";
print "================================\n";
print "Enter MySQL App Username: ";
my $dbapp_username=<STDIN>;
chomp $dbapp_username;
if($dbapp_username eq ''){ die "You must supply a MySQL App Username.  Installation Aborted!\n"; }

# get db app username
print "\n\n==============================\n";
print "= MySQL App Password to create =\n";
print "================================\n";
print "Enter MySQL App Password: ";
my $dbapp_password=<STDIN>;
chomp $dbapp_password;
if($dbapp_password eq ''){ die "You must supply a MySQL App Password.  Installation Aborted!\n"; }

# get mysql server name
print "\n\n=======================\n";
print "= Support Email Address =\n";
print "=========================\n";
print "If you don't know this yet, you can change it later.  The default will be: support\@localhost\n";
print "Enter support email address: ";
my $support_email=<STDIN>;
chomp $support_email;
if($support_email eq ''){ $support_email="support\@localhost"; }

# create database
print "Creating database...\n";
my $cmd="mysql -u $dbadmin_username -p$dbadmin_password -h $dbserver_hostname -e \"create database $dbname\"";
print "$cmd\n";
system($cmd);

print "Populating $dbname database...\n";
$cmd="mysql -u $dbadmin_username -p$dbadmin_password -h $dbserver_hostname $dbname < database/app.sql";
print "$cmd\n";
system($cmd);

print "Giving $dbapp_username access...\n";
$cmd="mysql -u $dbadmin_username -p$dbadmin_password -h $dbserver_hostname -e \"use $dbname; grant all on $dbname.\* to $dbapp_username\@localhost identified by '$dbapp_password'\"";
print "$cmd\n";
system($cmd);

# create configuration file based on information above
print "Creating app.config file based on settings supplied by this installer...\n";
open(my $fh, '>', "$wwwroot/difr/$app_name/app.config") or die "Cannot create configuration, $wwwroot/difr/$app_name : $!\n";
print $fh "\$g->{domainname}='$domain_name';";
print $fh "\$g->{sitename}='$app_title';";
print $fh "\$g->{site_slogan}='';";
print $fh "\$g->{site_logo}=\"http://www.etherfeat.com/themes/portal/logo.gif\";";
print $fh "\$g->{email_support}=\"$support_email\";";
print $fh "\$g->{email_support_display}=\"$support_email\";";
print $fh "\$g->{email_sales}=\"sales\\\@$g->{domainname}\";";
print $fh "\$g->{appname}='$app_name';";
print $fh "\$g->{themes}='themes';";
print $fh "\$g->{default_theme}=\"bootstrap\";";
print $fh "\$g->{modpath}=\"../../difr/$app_name/modules\";";
print $fh "\$g->{tempfiles}=\"../../difr/$app_name/temp\";";
print $fh "\$g->{sqlconf}=\"../../difr/$app_name/app.conn\";";
print $fh "\$g->{modhome}='interface_preferences';";
print $fh "\$g->{scriptname}=\"app.pl\";";
print $fh "\$g->{countryfile}='lib/country-codes.txt';";
close($fh);

# create legacy conn file for mysql connection
print "Creating legacy app.conn file (soon to be deprecated)...\n";
open(my $fh, '>', "$wwwroot/difr/$app_name/app.conn") or die "Cannot create conn file : $!\n";
print $fh "$dbname,$dbserver_hostname,$dbapp_username,$dbapp_password";
close($fh);

print "Installation complete!!\n\n";

print "Access your installation here: http://$domain_name/$subfolder\n\n";
