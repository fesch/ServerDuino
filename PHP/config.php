<?php
#
#    ServerDuino
#    (c) 2011 Charel Buchler <charel.buechler@caritas.lu>
#            Robert Fisch <robert.fisch@caritas.lu>
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

// Arduino Webserver (IP or hostname)
$arduino = "";
 
// Base Working Dir (we used /var/www/serverduino/)
$basedir ='';

$alertTemperatureThreshold = 30;
$alertLightPeriod = 300; // in minutes
$alertLightThreshold = 30;
$alertRecipents = array(''); // add e-mail adresses which should be alerted if something went wrong
?>
