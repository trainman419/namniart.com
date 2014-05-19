---
layout: post
title: "Installing ROS on the NVIDIA Jetson TK1"
excerpt: First look at ROS on the NVIDIA Jetson TK1
categories:
- Jetson-TK1
- Ubuntu
---

Notes on installing ROS on the NVIDIA Jetson TK1.

Getting the board booted was simple; just plug in a keyboard, HDMI and power! Getting a graphical environment was a little more difficult. It comes with the drivers on the board, but you have to run the installer script before theyre installed and active, as described in this [README](http://developer.download.nvidia.com/embedded/jetson/TK1/README.txt). Ive also found that I need to switch to the console and restart `lightdm` after initial boot before the graphical environment will come up properly.

The OS appears to be a stock or mostly-stock Ubuntu 14.04, pointed at the official ports.ubuntu.com repository, so it should be quite compatible with ROS once debs are built.

From there, I added trusty to my ARM repository, pulled in the latest python debs, and pointed the board at the repository. I also had to enable the `universe` repository in `/etc/apt/sources.list`, and then I was able to install the python debs to support ROS.

From there, I'm following the [ROS Indigo Source](http://wiki.ros.org/indigo/Installation/Source) instructions. I expect it to take quite a while to download and compile; I'll update back on the progress later.
