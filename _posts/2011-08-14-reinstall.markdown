---
layout: post
title: "Reinstall"
excerpt: "I've learned a few things; I think it's time to start fresh"
categories:
- robot
---

Since starting at Willow Garage, I've learned a number of things about ROS and about how to use it efficiently, so I'm going to start re-writing all of the software on my robot from scratch. A few things I've learned that make me think this is a good idea:

* Packaged builds of ROS are pre-built; no more compiling on my slow processor.
* The ROS navigation stack is really cool. I want to use it.
* rosserial is a new/better way to inegrate ROS with a microcontroller
* tf would eliminate the need for me to do coordinate transform math in a bunch of places
* actionlib is a better way to send movement commands to the base and wait for the results.

Additionaly, the robot has a few issues that I want to address:
* The microcontroller code sucks and locks up on occasion. I'd like to fix this.
* The current GPS data pipeline is inordinately long (GPS -> arduino -> reciever node -> gpsd -> gpsd node -> consumer nodes). I want to decipher GPS strings on the microcontroller.
* I want to make use of my sonars.


Step 1: Install Ubuntu

I started by buying a new CompactFlash card for my robot, so that I can keep the old OS around in case I ever need it again. Since my motherboard doesn't support booting from USB, and I don't have a CD drive, I set up my home network to provide the ubuntu image over the network via netboot, and proceeded to install Ubuntu 10.04.3 from that.

The first thing I wanted for development was easier remote access to the robot while I'm working on it. To that end, I picked up a newer wireless card at Defcon (Atheros AR5001X+), and installed it in place of the wireless card I had before. It uses a better driver than my previous card, so I was able to set it up so that it automatically associates with my home network when I'm at home, and creates an ad-hoc wireless network when it can't detect my home network.

The trick to making this setup work is the roaming mode that is built into wpa_supplicant. I have my network SSIDs and passwords configured in /etc/wpa_supplicant/wpa_supplicant.conf, and I have my wlan0 interface in /etc/network/interfaces set up like this: 

     auto wlan0
     iface wlan0 inet manual
        wpa-driver wext
        wpa-roam /etc/wpa_supplicant/wpa_supplicant.conf
     
     iface blue inet dhcp
     
     iface adhoc inet static
        address 192.168.2.1
        netmask 255.255.255.0
        network 192.168.2.0
        post-up /etc/init.d/dnsmasq start
        pre-down /etc/init.d/dnsmasq stop

And my wpa_supplicant.conf looks like this (passwords and hashes stripped to protect the guilty):

    ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev
    
    ap_scan=1
    
    network={
            ssid="Blue"
            #psk="asdf"
            psk=asdf
            id_str="blue"
            scan_ssid=1
    }
    
    network={
            id_str="adhoc"
            ssid="dagny"
            mode=1
            frequency=2412
            key_mgmt=NONE
    }

This seems to work quite well, at least for detecting the proper network configuration to use on bootup. I'm not sure how it will do if the robot boots up in my house and then drives out of wifi range.

Note also that I have ah-hoc mode set up with a static IP address, and set up to start dnsmasq when the robot is in ad-hoc mode. This means that any client that connects to the ad-hoc network can get an IP address and resolve the DNS name of the robot and anything else on the network. It's not perfect, but it makes connecting to the robot a lot easier. The previous OS only hosted an ad-hoc network (no automatic connection to my local wifi), and it made connecting to the robot to work possible almost anywhere.

I was really hoping that the new chipset would support starting an access point instead of ad-hoc mode, but it looks like the low-level driver settings for switching between managed mode and access point mode aren't available to wpa_supplicant at the moment. I've confirmed that this card can act as an access point when using the older madwifi driver, but I think an automatic mode switch will have to wait for another day.

Now that I have wireless working, I've installed ROS from debian packages according to [these instructions](http://www.ros.org/wiki/diamondback/Installation/Ubuntu)
