---
layout: post
title: "Pandaboard Wifi on Ubuntu 13.04"
excerpt: Getting wireless drivers working on the Pandaboard and Ubuntu
categories:
- pandaboard
- Ubuntu
---

I've spent about a week wrestling with the wireless drivers on my pandaboard. Some part of the latest update to either the kernel or the firmware for the wl1271 picked up a bug in the latest update.

After much searching and playing around with kernels and firmware, I found that the most recent build of the 3.2.0 kernel and firmware from Precise had WiFi support that actually worked.

I downloaded the following kernel and firmware debs from ports.ubuntu.com:
 * [linux-firmware\_1.79.11](http://ports.ubuntu.com/pool/main/l/linux-firmware/linux-firmware_1.79.11_all.deb)
 * [linux-headers-3.2.0-1444](http://ports.ubuntu.com/pool/main/l/linux-ti-omap4/linux-headers-3.2.0-1444_3.2.0-1444.63_armhf.deb)
 * [linux-headers-3.2.0-1444-omap4](http://ports.ubuntu.com/pool/main/l/linux-ti-omap4/linux-headers-3.2.0-1444-omap4_3.2.0-1444.63_armhf.deb)
 * [linux-image-3.2.0-1444-omap4](http://ports.ubuntu.com/pool/main/l/linux-ti-omap4/linux-image-3.2.0-1444-omap4_3.2.0-1444.63_armhf.deb)

I then installed them by hand with `dpkg -i *.deb`

Since the 3.2.0 kernel is an older version than what normally ships with Ubuntu Raring, I had to do some convincing to get the Ubuntu tools to installer the earlier kernel. I moved the newer kernels out of `/boot` and then invoked `flash-kernel` to install the desired kernel into the boot partition:

    cd /boot
    sudo mkdir old
    sudo mv -i vmlinux-3.5* old/
    sudo flash-kernel 3.2.0-1444-omap4

## Notes and deviations

Various notes and brain scribblings that may be useful:

 * If you have other versions of the kernel installed, you'll have to move those out of /boot as well.
 * The flash-kernel tool sorts kernel versions lexically, so it may sort things in an order you aren't expecting. If it complains that it's ignoring the version you gave it because there's a newer version of the kernel, move that version out of /boot and try again
 * On my board, /dev/mmcblk0p1 is used as the boot partition. If you mount it, you can modify the bootloader parameters in preEnv.txt and the kernel parameters in uEnv.txt
 * /dev/mmcblk0p1 is a vfat partition, so if you get into really serious trouble you can mount it on pretty much anything and load an older kernel or change the boot options
 * The bootloader (u-boot) presents a simple shell on the serial port during the first 3 seconds of boot. If you enter the bootloader, you can select the backup environment and kernel by using `setenv bootenv uEnv.txt.bak` . This _should_ work with any text file in the boot partition. Then run `boot` to load and boot the kernel.
 * For anyone still armel, there are armel versions of the 3.2.0 kernel that should work as well. **DO NOT** use these if you intent to use the hard-float EABI.
