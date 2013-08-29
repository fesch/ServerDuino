<?php
/**
 *    ServerDuino
 *     @author Charel Buchler <charel@charelbuchler.com>
 *    @author Robert Fisch <robert.fisch@caritas.lu>
 **/
 
 
// Arduino Webserver (IP or hostname)
$arduino = '';

// URL you want to send the E-Mail from
$smtpURL = ''; 

// Base Working Dir (we used /var/www/serverduino/)
$basedir ='';

$alertTemperatureThreshold = 30;
$alertLightPeriod = 300; // in minutes
$alertLightThreshold = 30;
$alertRecipents = array(''); // add e-mail adresses which should be alerted if something went wrong
?>
