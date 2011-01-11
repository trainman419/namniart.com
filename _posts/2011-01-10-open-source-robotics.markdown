---
layout: post
title: "Open Source Robotics Software"
excerpt: "Exploring the available open-source robotics software"
categories:
- robot
- senior project
- open source
---

As I continue to make slow, steady and relatively boring progress on the microcontroller firmware for my robot, it's time for me to start thinking about the software that will run on the main processor. Wikipedia has a good [list](http://en.wikipedia.org/wiki/Open-source_robotics) of the open-source robotics software that's out there, and I'm going to look more deeply at a few of the packages, in particular [ROS](http://www.ros.org/wiki/), [RoboComp](http://sourceforge.net/apps/mediawiki/robocomp/index.php?title=Main_Page), [Orca Robotics](http://orca-robotics.sourceforge.net/), and [The Orocos Project](http://www.orocos.org/orocos/whatis). The other packages weren't considered in depth, either because they're simulator packages, not suited to running a real robot, or they don't list support for my scanning laser range finder.

In particular, I'm looking for:
* Camera and/or Kinect drivers
* Vision algorithms of some sort
* Navigation and mapping frameworks
* Filtering algorithms

## ROS
ROS is the framework that I've heard the most about, and I've seen it referred to by more people, but popularity isn't everything. It has support for cameras, the Kinect, and OpenCV vision. It also contains path planners that look like they will work well with my non-holonomic robot base. I see a few simple mean and median filters, but I didn't see any Kalman filters. It looks like it contains good support for localization and map-making.

## The Orocos Project
Orocos advertises a strong Real-Time Toolkit, which includes fancy filtering algorithms such as Kalman filters and Bayesian filters. The emphasis seems to be on real-time control, and it looks like a number of the components can be used pre-made with a little configuration, without writing any software. They advertise using their framework as an extension or a companion to ROS. Their documentation is a bit fuzzy on how to use the modules that they supply, and I don't see much in the localization or mapping fields.

## Orca
Orca appears to be a fork of the Orocos Project. One thing that stands out is support for stereo vision, which is cool even if I'm nowhere close to using it on my senior project. They have a number of localization, mapping and path-planning modules, but don't seem to have the advanced filtering algorithms present in Orocos. 

## Conclusion
For now, I think I'll start by playing with ROS, and maybe adding Orocos if I need the advanced filtering capabilities. This is mostly motivated by the fact that it appears to be functionally equivalent to Orca, but has a larger user base, which should indicate a better code base to draw from.
