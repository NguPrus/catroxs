<?

//--------------------------------------------------------------------
// KS AntiDDOS ver. 5.0
// (C) Cosinus, Klavasoft.com
// LOOKING FOR MONITORING OF INTEGRITY FILES? VISIT http://ifube.com
//---------------------------------------------------------------------

/*
agssbuzz@catroxs.org
http://www.catroxs.org
*/



class ksantiddos 
{
	var $status, $error_msg;
	var $visitor; // status of visitor = raw|cool|warm|hot
	var $warm_level; // number of hits for last $seconds_limit seconds that cause visitor`s status turn to warm
	var $auto = true; // block visitors by KS AntiDDOS
	var $delay = 30; // seconds of delay of blocked visitors
	var $block_cnet = true; // block all C class net.

	function doit($hits_limit,$seconds_limit,$mysql_login,$pass,$dbname,$host='localhost')
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		try
		{
			$conn = mysql_connect($host,$mysql_login,$pass);
			$ok = mysql_select_db($dbname,$conn);
			if ($this->block_cnet) 
				$ip = substr($ip,0,strrpos($ip,'.')+1);
			$res = mysql_query("SELECT count(*) kount FROM ksantiddos WHERE ip='$ip' AND tstamp>".(time()-$seconds_limit));
			$row = mysql_fetch_assoc($res);
			if (!$row)
				$this->error_msg = 'Error detected';
			$count = $row['kount'];
			if ($count==1) // if only current hot in the list for the IP
				$this->visitor = "new";
			elseif ($count>$hits_limit)
				$this->visitor = "hot";
			elseif ($count>=$this->warm_level) 
				$this->visitor = "warm";
			else
				$this->visitor = "cool";
			if ($this->visitor!='hot')
			{
				// add current hit
				mysql_query("INSERT INTO ksantiddos SET ip='$ip', tstamp=".time());
				// cleanup  iplist
				mysql_query("DELETE FROM ksantiddos WHERE tstamp<".(time()-$seconds_limit));
			}
		}
		catch(Exception $e)
		{
			$this->error_msg = $e->getString();
			$this->status = 'error';
			return;
		}
		if (!empty($this->error_msg) )
		{
			$this->status = 'error';
		}
		if ($this->auto && $this->visitor=='hot')
		{
			header('HTTP/1.0 503 Service Unavailable');
			header('Status: 503 Service Unavailable');
			header("Retry-After: $this->delay");
			print "<html><meta http-equiv='refresh' content='$this->delay'><body><h2>Our server is currently overloaded, your request will be repeated automatically in $this->delay seconds</h2>";
			die();
		}		
	}
}


?>