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

Since I'm pretty keen on building debs so that everyone else on ARM doesn't have to, I took a break and built collada-dom-dev, openni and PCL for Trusty. I built collada and openni, and then started the PCL build and let it run overnight. It ran out of memeory the first time, but I was able to free up enough memory to get it to run by stopping Firefox and X.

Compilation time...
