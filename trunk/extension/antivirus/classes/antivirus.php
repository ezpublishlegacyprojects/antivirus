<?php
include_once("extension/ezpowerlib/ezpowerlib.php");
include_once( "Net/Socket.php");

class Antivirus
{
    function Antivirus()
    {
    	
    }
    function continueOnError()
    {

    }
    /**
     * Scans a single file or a whole directory structure, and stops at the first
	   infected file found.  The specified path must be absolute.  A scan mode may be
       specified: a mode of B<ClamAV::Client::SCAN_MODE_NORMAL> (which is the default)
       causes a normal scan (C<SCAN>) with archive support enabled, a mode of
       B<ClamAV::Client::SCAN_MODE_RAW> causes a raw scan with archive support
       disabled.

       If an infected file is found, returns a list consisting of the path of the file
       and the name of the malware signature that matched the file.  Otherwise,
       returns the originally specified path and B<undef>.
     *
     * @param string $file
     * @param boolean $rawScan
     * @return boolean
     */
	function hasNoVirus( $file, $doRawScan = false )
	{
		
		$ini = eZINI::instance( "clamav.ini" );

		$socket = new Net_Socket();
		
		$host = $ini->variable( "ClamAVSettings", "Host" ); 
		$port = $ini->variable( "ClamAVSettings", "Port" ); 
		// open connection
		if ( PEAR::isError( $socket->connect( $host, $port, true, 5 ) ) )
		{
		    eZDebug::writeError( 'No connection to anti virus deamon', 'Antivirus::hasNoVirus()' );
		    return null;
		}

		$root = eZSys::rootDir();
		$mode = $doRawScan ? "RAWSCAN" : "SCAN";
		$cmd = $mode . " " . $root . '/' . $file;
		eZDebug::writeDebug( $cmd, 'ClamAV request' );
		// Send data including linebreak
		$socket->writeLine( $cmd );

		// receive data until linebreak
		$result = $socket->readLine();
		eZDebug::writeDebug( $result, 'ClamAV response' );
		// close connection
		$socket->disconnect();
		if ( PEAR::isError( $result ) )
		{
		    if ( $ini->variable( "ClamAVSettings", "ContinueOnError" ) == 'enabled' )
                return true;
		    else
                return false;
		}
		if ( preg_match( "/^(.*): (?:OK|(.*) FOUND)$/i", $result, $matches ) )
		{
			if ( isset( $matches[2] ) ) // $matches[2] is name of virus 
			{
				return false;
			}
		}

		return true;
	}
}
?>