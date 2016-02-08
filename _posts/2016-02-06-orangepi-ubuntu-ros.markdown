---
layout: post
title: "ROS on the Orange Pi 2"
excerpt: Installing ROS on the Orange Pi 2
categories:
- ROS
- Ubuntu
---

I'm once again back to building small robots; and the ARM board of the day is the Orange Pi 2, so I'm going to install Ubuntu and ROS on it.

I had a 32GB microSD card around, so I used that. The `robot` variant of ROS requires about 900MB to install, the `desktop` variant requires about 1.5GB, and I recommend another several GB for logs and work space. I don't recommend a microSD card smaller than 16GB.

Since there are ROS binaries (debs) for Ubuntu and they usually work on the Ubunntu variants, I started by installing Lubuntu on my Orange Pi 2, from the [Orange Pi download page](http://www.orangepi.org/downloadresources/), and then installing it according to the [Orange Pi Quickstart Guide](http://www.orangepi.org/quickstart/start_ddcaed797a20bade87c2a52c91a206.html).


Once the OS was copied to the SD card, my Orange Pi 2 booted right up.

Since I have lots of FTDI cables, and very few spare HDMI monitors, so I did my setup over the serial debug port, but these commands should work just as well if you're using a keyboard and monitor.

## Change the passwords

The default username and password is `orangepi`; start by logging in and changing the password (the default root password is `orangepi`; change that too)

## Resize the disk

Resize the partition. Run parted:
`$ parted`

Show the partition list:
`(parted) print`

There should be two partitions; 1 should be a boot partition, about 64MB; the second should be the main partition, about 3.6G

Resize the second partition to use the whole disk:
`(parted) resizepart 2 32GB`

Close parted:
`(parted) quit`

Reboot to load the updated partition table:
`$ reboot`

Log in as root again, resize the filesystem:
`$ resize2fs /dev/mmblk0p2`

Now you should have the full capacity of your microSD card available.

## Set up WiFi

If your network needs a passphrase, generate the hash for it using `wpa_passphrase`:

`$ wpa_passphrase Robonet`
`yournetworkpassword`

Then set up your `/etc/network/interfaces` to auto-join the network:

    auto wlan1
    iface wlan1 inet dhcp
      pre-up rfkill unblock 1
      wpa-ssid Robonet
      wpa-psk KEYKEYKEY # replace with the key from wpa_passphrase

And finally reboot to activate the new wifi configuration.
 (if you don't feel like rebooting, you can restart network-manager and do `ifup wlan1`)

I also had to disable network-manager, and reconfigure `eth0` with `allow-hotplug` in `/etc/network/interfaces`

## Install ROS

Install ROS by following the [ROS for Ubuntu ARM install guide](http://wiki.ros.org/indigo/Installation/UbuntuARM)


## Follow-up

The default Lubuntu build doesn't include FTDI drivers and many other drivers are probably missing, too.

Hokuyos, FTDI cables, some wireless adapters, and some other devices probably won't work.
