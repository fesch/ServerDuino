<?php
#
#    ServerDuino (c) 2010 Charel Buchler <charel.buechler@caritas.lu>
#    This file is part of ServerDuino
#
#    This is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    ServerDuino is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#     @package: rufus/caridas2
#     @subpackage: modules
#     @module: serverduino
#     @submodule: getData


include('config.php');

// enable reporting
error_reporting(0);

// setting the right directory
chdir($basedir);

/**
 * GETTING THE DATA
 **/

// Create a curl handle
$ch = curl_init($arduino);

// Execute
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$json_data = curl_exec($ch);

// Check if any error occured
if(curl_errno($ch)){die('Could not retrieve data');}

// Close handle
curl_close($ch); 

// Handling the Data we received from the DUINO.
$jsonObj = json_decode($json_data); // Decoding the JSON String we're receiving from the duino

// Handling json data;
$temprature = $jsonObj->{'tmp'}; // Temperature
$pressure = $jsonObj->{'pressure'}; // Peressure
$light = $jsonObj->{'light'}; // Light 
$tightness = $jsonObj->{'tightness'}; // Tightness
$date = date("m.d.y H:i:s"); // Date & Time


/**
 * UPDATE DATABASE
 **/

// syntax of $output = <temperature>:<pressure>:<humidity>:<light>
$output =  $temprature.":".$pressure.":".$tightness.":".$light;

`/usr/bin/rrdtool update arduinoDaily.rrd -t temperature:pressure:humidity:light N:$output`;
`/usr/bin/rrdtool update arduinoWeekly.rrd -t temperature:pressure:humidity:light N:$output`;
`/usr/bin/rrdtool update arduinoMonthly.rrd -t temperature:pressure:humidity:light N:$output`;
`/usr/bin/rrdtool update arduinoYearly.rrd -t temperature:pressure:humidity:light N:$output`;

/**
 * PLOT GRAPHS
 **/

plotGraph('arduinoDaily.rrd','ServerDuino Daily Statistic','d');
plotGraph('arduinoWeekly.rrd','ServerDuino Weekly Statistic','w');
plotGraph('arduinoMonthly.rrd','ServerDuino Monthly Statistic','m');
plotGraph('arduinoYearly.rrd','ServerDuino Yearly Statistic','y');



$light_values = getLightForPeriod($alertLightPeriod);
// @todo need to implement the last five values.. //still need to figure this out
if($temprature >= $alertTemperatureThreshold){
    send_alert('Subject: [Serverduino] Notice',
               'ALERT: IMPORTANT THE TEMPREATURE IS OVER 30°C IN THE SERVER ROOM');
}

// NO !! All the values must be greater than 20!!
// really all of them !
// The values in the *entire* array are for the past 300 minutes (=5 hours)

// use $alertLightThreshold

//if(($light_values[0] > 20) && ($light_values[1] > 20) &&  ($light_values[2] > 20) && ($light_value[3] > 20) && ($light_values[4] > 20)){
//    send_alert('Subject: [Serverduino] Notice','ALERT: LIGHT IS ON FOR THE LAST FIVE HOURS');
//}



/*****
 * FUNCTIONS
 */

/**
 * function to plot a graph
 *
 * @param    $archive        the RRD file to use
 * @param    $title            the title to print on the graph
 * @param    $intervall        the intervall to use = {d|w|m|y}
 * @param    $destination    the destination directory (default = the current corking directory)
 */
function plotGraph($archive,$title,$intervall,$destination='.')
{
    // yes, this is *one* command to execute
    `/usr/bin/rrdtool graph $destination/graph_$intervall.png --title="$title" -E DEF:temperature=$archive:temperature:AVERAGE DEF:pressure=$archive:pressure:AVERAGE CDEF:pressureMOD=pressure,-1000,+ DEF:humidity=$archive:humidity:AVERAGE DEF:light=$archive:light:AVERAGE VDEF:temperatureMAX=temperature,MAXIMUM VDEF:temperatureMIN=temperature,MINIMUM VDEF:temperatureAVG=temperature,AVERAGE VDEF:temperatureCUR=temperature,LAST VDEF:pressureMAX=pressure,MAXIMUM VDEF:pressureMIN=pressure,MINIMUM VDEF:pressureAVG=pressure,AVERAGE VDEF:pressureCUR=pressure,LAST VDEF:humidityMAX=humidity,MAXIMUM VDEF:humidityMIN=humidity,MINIMUM VDEF:humidityAVG=humidity,AVERAGE VDEF:humidityCUR=humidity,LAST VDEF:lightMAX=light,MAXIMUM VDEF:lightMIN=light,MINIMUM VDEF:lightAVG=light,AVERAGE VDEF:lightCUR=light,LAST COMMENT:"\t\t\t\t   Current   Minimum   Maximum   Average\l" LINE2:temperature#FF0000:"temperature (°C)\t\t" GPRINT:temperatureCUR:"%7.2lf\t" GPRINT:temperatureMIN:"%7.2lf\t" GPRINT:temperatureMAX:"%7.2lf\t" GPRINT:temperatureAVG:"%7.2lf\l" LINE2:pressureMOD#00FF00:"pressure (hPa)\t\t"  GPRINT:pressureCUR:"%7.2lf\t" GPRINT:pressureMIN:"%7.2lf\t" GPRINT:pressureMAX:"%7.2lf\t" GPRINT:pressureAVG:"%7.2lf\l" LINE2:humidity#0000FF:"humidity (%)\t\t" GPRINT:humidityCUR:"%7.2lf\t" GPRINT:humidityMIN:"%7.2lf\t" GPRINT:humidityMAX:"%7.2lf\t" GPRINT:humidityAVG:"%7.2lf\l" LINE2:light#fe9d29:"light (%)\t\t\t" GPRINT:lightCUR:"%7.2lf\t" GPRINT:lightMIN:"%7.2lf\t" GPRINT:lightMAX:"%7.2lf\t" GPRINT:lightAVG:"%7.2lf\l" -w 800 -h 200 -s -1$intervall -l 0`;
}

/**
 * function to send an alert message
 *
 * @param    $message    the message to be send
 */
function send_alert($subject,$message)
{ 
    global $alertRecipients;
    
    $header=array();
    $to = $alertRecipients;
    $smtp=fsockopen ('192.10.50.9', 25, $errno, $errstr, 30 );
    if (!$smtp)
    {
        echo "$errno - $errstr";
    }
    else
    {
        fputs ($smtp,"HELO php.sendmail\r\n" ); flush();
        fgets($smtp,1024)."<br>"; flush();
        fputs ($smtp,"MAIL FROM: <mango@caritas.lu>\r\n" ); flush();
        fgets($smtp,1024)."<br>"; flush();
        foreach($to as $t)
        {
            fputs ($smtp,"RCPT TO: <$t>\r\n" ); flush();
            fgets($smtp,1024)."<br>"; flush();
        }
        fputs ($smtp,"DATA\r\n"); flush();
        fgets($smtp,1024)."<br>"; flush();
        fputs ($smtp,$subject."\r\n" ); flush();
        //fputs ($smtp,"Cc: $cc\r\n" ); flush();
        //fputs ($smtp,"Bcc: $bcc\r\n" ); flush();
        foreach($header as $h)
        {
            fputs ($smtp,"$h\r\n" ); flush();
        }
        fputs ($smtp,"\r\n" ); flush();
        fputs ($smtp,"".$message." \r\n" ); flush();
        fputs ($smtp,".\r\n" ); flush();
        fgets($smtp,1024)."<br>"; flush();
        fputs ($smtp,"QUIT" ); flush();
        //fgets($smtp,1024)."<br>"; flush();
        fclose($smtp); 
    }
}

/**
 * function to retrieve the light values for a given period from the weekly archive
 *
 * @param    $period        the period specified in minutes
 * @return                an array with all the values for the given period
 */
function getLightForPeriod($period)
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



?>
