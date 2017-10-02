# Installation Guide - RAPTOR

## Overview



## Section 1 - Setting up the VistA Server



### Enabling the RedHat Optional Repository

If it can't find the repository 'rhui-REGION-rhel-server-optional', then execute the following:

``` bash
grep -B1 -i optional /etc/yum.repos.d/*
```

Select the 'optional' repository listed (id is within the square brackets) - you DO NOT want 'source-optional'

Replace 'rhui-REGION-rhel-server-optional' in the command above, with the id from inside the brackets []

Re-execute the command above with the new repository id.

``` bash
sudo yum-config-manager --enable <redhat-optional-repository-id>
```

### Setup Cache and Permissions

### Installing Cache


