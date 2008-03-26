<code>
/*
    Antivirus ( ClamAV ) integration for eZ publish
    Copyright (C) 2005-2007  xrow GbR, Hannover Germany, http://xrow.de

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/

### BEGIN INIT INFO
# Provides:     antivirus
# Depends:		( ezpowerlib | Net_Socket )
# OS:			Linux, FreeBSD
# Version:		< eZ 3.7		
# Developed:	Björn Dieding  ( bjoern@xrow.de )
# Short-Description: virus scanner daemon
# Description:       Integration of clamd/clamav virus scanner daemon for downloads files
# Resources:	http://www.clamav.net/, http://pubsvn.ez.no/community/trunk/extension/antivirus/
### END INIT INFO
</code>

#### Setup ####

activate extension

#### Usage ####

1.)
There is a workflow present that can check all uploaded files before publish.

2.)
Any time a webuser downlods a file over content/download it gets checked via the clamd deamon for viruses.

#### Troubleshooting ####

a.) Testing avaialablity of the Daemon

<code>
web1@p15200145:/> telnet localhost 3310
Trying 127.0.0.1...
Connected to localhost.
Escape character is '^]'.
SCAN /home/httpd/vhosts/xrow.de/httpdocs/var/storage/original/application/eZWebDAVUpload_yPejc6.zip
/home/httpd/vhosts/xrow.de/httpdocs/var/storage/original/application/eZWebDAVUpload_yPejc6.zip: OK
Connection closed by foreign host.
web1@p15200145:/>
</code>

b.) Resolving permission problems / No Access

By default the daemon run under the user calmav, vscan or else. 
Your permissions might not allow that this user has access to scanned files.
In this case you need to run clamd under the root user or move the clamd user
 into the group of for example apache.

Try to avoid running this service as root.
 
c.) Testing with a test virus

create a text file with the following string
<code>
X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*
</code>
Upload it into eZ publish and download it. The download should be not avialable now.