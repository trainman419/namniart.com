---
layout: post
title: "Software, Part 1"
excerpt: "The first of many posts on robot software"
categories:
- robot
- senior project
---

Since I'm running gentoo on the main computer on my robot, I followed these [installation instructions](http://www.ros.org/wiki/cturtle/Installation/Gentoo) for ROS. I ran updates on my gentoo machine before installing ROS, so I had ample time to read the documentation.

I decided to do the "Base Install" of ROS to get the navigation stacks. I had to unmask 'dev-libs/log4cxx', and add 'extra' to the use flags for app-text/texlive. (and on my desktop, unmask media-libs/libmpdclient-2.3). I also had to use eselect to enable wxWidgets before the install would complete successfully.

After successfully installing all of the dependencies for ros, and then installing ROS itself, it's time to build the parts of ROS that I'm acutally going to use. I ran 'rosmake hokuyo_node' to build my laser driver. I had a hard time compiling the built-in visualzation tools from ROS, so I wrote a simple laser scan visualizer of my own; I didn't know python, but I know pygame is a very easy way to do graphics in python, so I used in to write a ROS node to receive and plot laser scan data; the result works quite well, and is available [here](https://github.com/trainman419/Senior-Project/tree/master/ros/laser_view).

Since I got ROS running on my main computer, I've been focusing on software development; all of my code is publicly available on [github](https://github.com/trainman419/Senior-Project), along with some of my documentation and paperwork for the project. The arduino directory is the code that runs my arduino, and manages most of the hardware and low-level communication on the robot. Control is the software that I'm writing for high-level debugging and control; right now, the viewer that's in there receives laser scanner data over and bluetooth serial connetion, and displays it to a window, while also accepting keyboard input and providing rudimentary motor control. The ros directory contains my ROS nodes, including my laser scan viewer, my hardware interface node that reads the laser scanner and transmits the data out the serial port to be transmitted over bluetooth, and my launch file, to tie everything together.

I've spent most of the last few weeks working on the Arduino firmware, getting the serial communication routines right, working on the serial protocols that will be used to communicate with both the bluetooth debugging station and the main CPU on the robot itself. I've also been working on porting my RTOS from my old Polybot board to the new Arduino. Since the controller on the ATMega2560 has 256k of program space, it has a 3-byte program counter instead of a 2-byte program counter, which I had to modify my RTOS to deal with, and which ended in me making significant speed and code complexity improvements in the task switcher.

I also spent a little time learning python and writing my debugging console in pthon with pygame. My use of pygame may seem a little odd, given that this isn't a game, but the framework provided by pygame makes it very easy to display arbitrary, computer-generated graphics, and very easy to accept user input to provide basic robot control.

Overall, I'm just starting to get to the point where my robot has enough data to do interesting things on its own. My goal for the coming weekend is to be able to get GPS data from the GPS to the main controller, and be able to follow a pre-programmed series of waypoints, ignoring obstacles. This also includes working out the kinematics of the robot, and developing a motion control strategy similar to what we're using the autonomous mobile robots course that I'm taking.
