<?php

/**
 *
 * 
 * 
 **/

class ServerDuino{
	
	protected $config;
	
	/**
	 * 
	 *
	 *
	 **/
	public function __construct($config){
		$this->config = $config;
	}
	
	public function run() {
		$json_data = $this->fetch_data();
		$this->prepare_data($json_data);
		$this->plot_js_graph();
	}
	/**
	 * 
	 *
	 *
	 **/
	
	public function fetch_data(){
		// Create a curl handle
		$http_con = curl_init($this->config["hostname"]);
		// Execute
		curl_setopt($http_con, CURLOPT_RETURNTRANSFER, true);
		$json_data = curl_exec($http_con);
		// Check if any error occured
		if(curl_errno($http_con)){die('Could not retrieve data');}
		// Close handle
		curl_close($http_con);
		return $json_data;
	}
	
	public function prepare_data($json_data){
		// Handling the Data we received from the DUINO.
		$jsonObj = json_decode($json_data); // Decoding the JSON String we're receiving from the duino

		// Handling json data;
		$temprature = $jsonObj->{'tmp'}; // Temperature
		$pressure = $jsonObj->{'pressure'}; // Peressure
		$light = $jsonObj->{'light'}; // Light 
		$tightness = $jsonObj->{'tightness'}; // Tightness
		$date = date("m.d.y H:i:s"); // Date & Time
		
		$db_connect = mysql_connect($dbhost,$dbuser,$dbpassword);
		$thedb = mysql_select_db($dbname,$db_connect);
		$insertQuery = "INSERT INTO tblData (dtTemp, dtPressure, dtLight, dtTightness, dtDate) VALUES(".$temprature.",".$pressure.",".$light.",".$tightness.",".$date")"); 
		$execQuery = mysql_query($insertQuery);
		if(!$execQuery){
			print(mysql_error()." ".mysql_errno());
		}
	}
	
	/**
	 * 
	 *
	 *
	 **/
	public function update_rrd(){
		
		$db_connect = mysql_connect($dbhost,$dbuser,$dbpassword);
		$thedb = mysql_select_db($dbname,$db_connect);
		$selectQuery = "SELECT * FROM tblDate WHERE dtDate like % ".date('m.d.y')."";
		
		// syntax of $output = <temperature>:<pressure>:<humidity>:<light>
		$output =  $temprature.":".$pressure.":".$tightness.":".$light;

		`/usr/bin/rrdtool update arduinoDaily.rrd -t temperature:pressure:humidity:light N:$output`;
		`/usr/bin/rrdtool update arduinoWeekly.rrd -t temperature:pressure:humidity:light N:$output`;
		`/usr/bin/rrdtool update arduinoMonthly.rrd -t temperature:pressure:humidity:light N:$output`;
		`/usr/bin/rrdtool update arduinoYearly.rrd -t temperature:pressure:humidity:light N:$output`;
		
	}
	
	public function plot_js_graph(){
		$db_connect = mysql_connect($dbhost,$dbuser,$dbpassword);
		$thedb = mysql_select_db($dbname,$db_connect);
		$selectQuery = "SELECT * FROM tblDate WHERE dtDate like '".date('m.d.y')." %'";
		$execSelect = mysql_query($selectQuery);
		$json_parse_data = while($fetchdata = mysql_fetch_object($execSelect)){
			
		}
		json_encode($json_parse_data);
		
	}
	
	
	
	/**
	 * 
	 *
	 *
	 **/
	public function plot_graph($archive,$title,$intervall,$destination='.'){
		// yes, this is *one* command to execute
	    `/usr/bin/rrdtool graph $destination/graph_$intervall.png --title="$title" -E DEF:temperature=$archive:temperature:AVERAGE DEF:pressure=$archive:pressure:AVERAGE CDEF:pressureMOD=pressure,-1000,+ DEF:humidity=$archive:humidity:AVERAGE DEF:light=$archive:light:AVERAGE VDEF:temperatureMAX=temperature,MAXIMUM VDEF:temperatureMIN=temperature,MINIMUM VDEF:temperatureAVG=temperature,AVERAGE VDEF:temperatureCUR=temperature,LAST VDEF:pressureMAX=pressure,MAXIMUM VDEF:pressureMIN=pressure,MINIMUM VDEF:pressureAVG=pressure,AVERAGE VDEF:pressureCUR=pressure,LAST VDEF:humidityMAX=humidity,MAXIMUM VDEF:humidityMIN=humidity,MINIMUM VDEF:humidityAVG=humidity,AVERAGE VDEF:humidityCUR=humidity,LAST VDEF:lightMAX=light,MAXIMUM VDEF:lightMIN=light,MINIMUM VDEF:lightAVG=light,AVERAGE VDEF:lightCUR=light,LAST COMMENT:"\t\t\t\t   Current   Minimum   Maximum   Average\l" LINE2:temperature#FF0000:"temperature (°C)\t\t" GPRINT:temperatureCUR:"%7.2lf\t" GPRINT:temperatureMIN:"%7.2lf\t" GPRINT:temperatureMAX:"%7.2lf\t" GPRINT:temperatureAVG:"%7.2lf\l" LINE2:pressureMOD#00FF00:"pressure (hPa)\t\t"  GPRINT:pressureCUR:"%7.2lf\t" GPRINT:pressureMIN:"%7.2lf\t" GPRINT:pressureMAX:"%7.2lf\t" GPRINT:pressureAVG:"%7.2lf\l" LINE2:humidity#0000FF:"humidity (%)\t\t" GPRINT:humidityCUR:"%7.2lf\t" GPRINT:humidityMIN:"%7.2lf\t" GPRINT:humidityMAX:"%7.2lf\t" GPRINT:humidityAVG:"%7.2lf\l" LINE2:light#fe9d29:"light (%)\t\t\t" GPRINT:lightCUR:"%7.2lf\t" GPRINT:lightMIN:"%7.2lf\t" GPRINT:lightMAX:"%7.2lf\t" GPRINT:lightAVG:"%7.2lf\l" -w 800 -h 200 -s -1$intervall -l 0`;
	}
	/**
	 * 
	 *
	 *
	 **/
	public function send_alert($subject,$message){
		    $this->_reason = $subjct;
			$this->_msg = $message;
		
		if(function_exists('mail')){
			return mail($alertRecipients,$this->_reason,$this->_msg);
		}else{
		
		global $alertRecipients;

		    $header=array();
		    $to = $alertRecipients;
		    $smtp=fsockopen ($smtpURL, 25, $errno, $errstr, 30 );
		    if (!$smtp)
		    {
		        echo "$errno - $errstr";
		    }
		    else
		    {
		        fputs ($smtp,"HELO php.sendmail\r\n" ); flush();
		        fgets($smtp,1024)."<br>"; flush();
		        fputs ($smtp,"MAIL FROM: <serverduino@systems.caritas.lu>\r\n" ); flush();
		        fgets($smtp,1024)."<br>"; flush();
		        foreach($to as $t)
		        {
		            fputs ($smtp,"RCPT TO: <$t>\r\n" ); flush();
		            fgets($smtp,1024)."<br>"; flush();
		        }
		        fputs ($smtp,"DATA\r\n"); flush();
		        fgets($smtp,1024)."<br>"; flush();
		        fputs ($smtp,$this->_reason."\r\n" ); flush();
		        //fputs ($smtp,"Cc: $cc\r\n" ); flush();
		        //fputs ($smtp,"Bcc: $bcc\r\n" ); flush();
		        foreach($header as $h)
		        {
		            fputs ($smtp,"$h\r\n" ); flush();
		        }
		        fputs ($smtp,"\r\n" ); flush();
		        fputs ($smtp,"".$this->_msg." \r\n" ); flush();
		        fputs ($smtp,".\r\n" ); flush();
		        fgets($smtp,1024)."<br>"; flush();
		        fputs ($smtp,"QUIT" ); flush();
		        //fgets($smtp,1024)."<br>"; flush();
		        fclose($smtp); 
		    }
		}
	}
	public function getLightForPeriod($period)
	{
	    $tail = ceil($period/18)+1;  // 18 because sampling each 3 minutes with 
	                                 // 6 samles per average for the weekly archive
	                                 // one more line because the last line is not valid
	    $head = $tail-1;

	    $lines = `rrdtool fetch arduinoWeekly.rrd AVERAGE | tail -n $tail | head -n $head`;
	    // split by linebreak
	    $lines = explode("\n",trim($lines));

	    $result = array();

	    // test input
	    foreach($lines as $line)
	    {
	        // split line
	        $values = explode(' ',$line);
	        // the last value of each line is the lightvalue
	        // so add it to the array
	        $result[]=(float)$values[4];
	    }

	    return $result;
	}
	
	
}

?>