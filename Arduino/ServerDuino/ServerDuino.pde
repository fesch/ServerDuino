/*#
#    ServerDuino
#   (c) 2011 Charel Buchler <charel.buechler@caritas.lu>
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
*/

/*
  Web  Server
 
 A simple web server that shows the value of the analog input pins.
 using an Arduino Wiznet Ethernet shield. 
 
 Circuit:
 * Ethernet shield attached to pins 10, 11, 12, 13
 * Analog inputs attached to pins A0 through A5 (optional)
 
 created 18 Dec 2009
 by David A. Mellis
 modified 4 Sep 2010
 by Tom Igoe
 
 */

#include <SPI.h>
#include <Ethernet.h>
#include <BMP085.h>
#include <Wire.h>



// define the BMP085 sensor
BMP085 bmp = BMP085();
// ... and it's return variables

long Temperature = 0, Pressure = 0;

// Enter a MAC address and IP address for your controller below.
// The IP address will be dependent on your local network:
byte mac[] = { 0x90, 0xA2, 0xDA, 0x00, 0x62, 0xE9 };
byte ip[] = { 192,10,50, 6 };

// Initialize the Ethernet server library
// with the IP address and port you want to use 
// (port 80 is default for HTTP):
Server server(80);

void setup()
{
  // start the Ethernet connection and the server:
  Ethernet.begin(mac, ip);
  server.begin();
  
  // init the BMP085 sensor
  bmp.init(MODE_STANDARD, 25000, true);
}

/**
 * getTemp
 *
 * @return: float 
 *
 **/

float getTmp(){
  bmp.getTemperature(&Temperature);
return Temperature*0.1;
}

/**
 * getPressure
 *
 * @return: float
 *
 **/
 
float getPressure(){// load the WIRE library
 // get temperature and pressure
  bmp.getPressure(&Pressure);
  return Pressure*0.01;
}

/**
 * getPressure
 *
 * @return: float
 *
 **/

float getLight(){
 return (analogRead(0) / 1024.0)*100; 
}

/**
 * getPressure
 *
 * @return: float
 *
 **/

float getTightness(float temp){
  // fix temperature (if you can get it from a temperatur sensor, please do so!)
 
  int humPin = 1;
  // read the value from the pin
  int humReading = analogRead(humPin); 
  // convert it into voltage (Vcc = 5V)
  double volt = humReading / 1023.0 * 5;
  // calculate the sensor humitidy
  double sensorRH = 161.*volt/5 - 25.8;
  // adapt this for the given temperature
  double trueRH = sensorRH / (1.0546 - 0.0026*temp);  

  return trueRH;
}

// the loop
void loop()
{
  // listen for incoming clients
  Client client = server.available();
  if (client) {
    // an http request ends with a blank line
    boolean currentLineIsBlank = true;
   while (client.connected()) {
      if (client.available()) {
        char c = client.read();
        // if you've gotten to the end of the line (received a newline
        // character) and the line is blank, the http request has ended,
        // so you can send a reply
        if (c == '\n' && currentLineIsBlank) {
          // send a standard http response header
          client.println("HTTP/1.1 200 OK");
          client.println("Content-Type: text/plain");
          client.println();

          float tightTemp = getTmp(); 
          // output the value of each analog input pin
          client.print("{\"tmp\":\"");     
          client.print(tightTemp);
          client.print("\"");
          client.print(",");
          client.print("\"pressure\"");
          client.print(":");
          client.print("\"");
          client.print(getPressure());
          client.print("\",");
          client.print("\"light\":");
          client.print("\"");
          client.print(getLight());
          client.print("\"");
          client.print(",");
          client.print("\"tightness\":");
          client.print("\"");
          client.print(getTightness(tightTemp));
          client.print("\"");
          client.print("}");
          break;
        }
        if (c == '\n') {
          // you're starting a new line
          currentLineIsBlank = true;
        } 
        else if (c != '\r') {
          // you've gotten a character on the current line
          currentLineIsBlank = false;
        }
      }
    } // give the web browser time to receive the data
    delay(1);
    // close the connection:
    client.stop();
  }
}

