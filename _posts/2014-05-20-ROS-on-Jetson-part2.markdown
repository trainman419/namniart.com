---
layout: post
title: "ROS on the Jetson TK1 - part 2"
excerpt: Building ROS on the NVIDIA Jetson TK1
categories:
- Jetson-TK1
- Ubuntu
---

Notes on installing ROS on the NVIDIA Jetson TK1.

I'm starting out trying the ROS desktop-full variant. This the first ARM board I've seen with a good GPU and good GPU drivers on it, so I have some hope that it may actually be able to run RVIZ.

Downloading ROS itself went smoothly. I was able to install most of the dependencies, but there were a few dependencies that weren't staisfied.
I got this list by running `rosdep install --from-paths src --ignore-src --rosdistro indigo -y -r`, which installed everything that WAS available and then informed me about which dependnecies it wasn't able to find:

 * gazebo2
 * sbcl
 * libpcl-1.7-all
 * libpcl-1.7-all-dev
 * collada-dom-dev

Getting all of the dependencies installed took about an hour or two of downloading and unpacking packages. This is a great chance to do your laundry or make a sandwich.

Since I'm pretty keen on building debs so that everyone else on ARM doesn't have to, I took a break and built collada-dom-dev, openni and PCL for Trusty. I built collada and openni, and then started the PCL build and let it run overnight. It ran out of memeory trying to build PCL, so I guess I'll have to put that off and build it on my build farm when it's back up and running.

I'm now left with the following unmet dependencies:

 * gazebo2
 * sbcl
 * libpcl-1.7-all and libpcl-1.7-all-dev

I manually removed the packages that depend on these from my ros\_catkin\_ws/src folder:

 * sbcl:
  * roslisp
 * gazebo2:
  * gazebo\_ros\_pkgs
  * metapackages/simulators
 * libpcl-1.7-all:
  * pcl\_conversions
  * perception\_pcl

Since one of the big selling points of this board for robotics and ROS is the OpenCV acceleration, I went ahead and remove all of the stock libopen\* debs and installed the libopencv4tegra and libopencv4tegra-dev debs from the [developer site](https://developer.nvidia.com/jetson-tk1-support). Note that you'll have to apply and be admitted to the CUDA Registered Developer Program before you can download the accelerated CUDA and OpenCV libraries.

It looks like the CMake files for the TK1-optimized OpenCV libraries don't set OpenCV\_INCLUDE\_DIRS properly, which causes cv\_bridge to pass these bad include directories on to its downstream dependencies.

Once I fixed the OpenCV\_INCLUDE\_DIRS bug (and opened a ticket about it with Nvidia), cv\_bridge compiled properly and its dependencies compiled as well.

I was also able to install lm-sensors to monitor the temperature during the build; it looks like they have a few standard temperature sensors on-die.

The last stages of the ROS compilation are running now, but I'm optimistic that they'll complete successfully. In the meantime, I should get some sleep.
