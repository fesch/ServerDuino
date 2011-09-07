# Serverduino - Setup instructions

## TOC
1. Hardware (Arduino)
2. Microcontroller (Arduino)
3. Database (rrdtool)
4. CRON job (PHP script)

## 1. Hardware
You need to connect the different sensors to the Arduino board.
You may either use breadboard with some cords or solder everything 
onto a shield.

### The light sensor
Follow the instructions from the URL beneath but connect the light sensor 
to the analog pin 0.

http://arduino.fisch.lu/index.php?menu=12&page=&portal=9

### Humidity sensor
Follow the instructions from the URL beneath but connect the humidity sensor
to the analog pin 1.

http://arduino.fisch.lu/index.php?menu=17&page=&portal=9

### Baromethric pressure & temperature sensor
Follow the instructions from the URL beneath.

http://arduino.fisch.lu/index.php?menu=31&page=&portal=9

## 2. Microcontroller
Next you need to upload the controller sketch to the Arduino. In order
to be able to compile it, you have to install the BMP085 library into
the Arduino IDE. You can get it here:

http://code.google.com/p/bmp085driver/

## 3. Database
All data captured by the CRON script will be put into an RRD database.
For this, you need to setup 4 files. Move into the directory where you
want them to stay and execute the 4 commands:

	rrdtool create arduinoDaily.rrd --start N --step 180 DS:temperature:GAUGE:360:-10:60 DS:pressure:GAUGE:360:900:1200 DS:humidity:GAUGE:360:0:100 DS:light:GAUGE:360:0:100 RRA:MIN:0.5:1:480 RRA:MAX:0.5:1:480 RRA:AVERAGE:0.5:1:480 
	
	rrdtool create arduinoWeekly.rrd --start N --step 180 DS:temperature:GAUGE:360:-10:60 DS:pressure:GAUGE:360:900:1200 DS:humidity:GAUGE:360:0:100 DS:light:GAUGE:360:0:100 RRA:MIN:0.5:6:560 RRA:MAX:0.5:6:560 RRA:AVERAGE:0.5:6:560 

	rrdtool create arduinoMonthly.rrd --start N --step 180 DS:temperature:GAUGE:360:-10:60 DS:pressure:GAUGE:360:900:1200 DS:humidity:GAUGE:360:0:100 DS:light:GAUGE:360:0:100 RRA:MIN:0.5:20:720 RRA:MAX:0.5:20:720 RRA:AVERAGE:0.5:20:720 

	rrdtool create arduinoYearly.rrd --start N --step 180 DS:temperature:GAUGE:360:-10:60 DS:pressure:GAUGE:360:900:1200 DS:humidity:GAUGE:360:0:100 DS:light:GAUGE:360:0:100 RRA:MIN:0.5:240:730 RRA:MAX:0.5:240:730 RRA:AVERAGE:0.5:240:730 


## 4. CRON job
