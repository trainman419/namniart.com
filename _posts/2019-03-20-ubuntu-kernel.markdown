---
layout: post
title: "Dissecting the Ubuntu Kernel Packaging System"
excerpt: "How a kernel becomes ubuntu kernel packages"
categories:
- Linux
---

# Dissecting the Ubuntu Kernel Packaging System

For a project at my day job, I need to build a custom version of the linux kernel and package it in a way that is compatible with Ubuntu. The Linux kernel includes some build rules for debian packages, but these don't seem to work very well ( `make bindeb-pkg` works, but `make deb-pkg` doesn't). The debian packaging rules also omit the kernel debug symbols and the perf tools packages, which we need.

I did a similar packaging effort when I worked for Willow Garage, but that was over 5 years ago, and all I remember was that it was complicated, so I'm going to take detailed notes this time through.

This is written mostly as notes and a roadmap for myself. It assumes a relatively deep understanding of debian packaging, and a familiarity with the kernel and it's build and configuration systems.

I'm going to start with the directions from https://wiki.ubuntu.com/Kernel/BuildYourOwnKernel . Roughly the plan is:

 * Check out `git://kernel.ubuntu.com/ubuntu/ubuntu-precise.git`
 * Get a rough understanding of the packaging rules in that repository
 * Transplant those packaging rules onto my kernel
 * Trim out most of the kernel configuration variants
 * Build one (or maybe two) kernel variants, and the supporting packages.

## `debian` and `debian.master` 

The upstream repository contains `debian` and `debian.master`

Listing of `debian`:

    $ tree debian
    debian
    ├── commit-templates
    │   ├── bumpabi
    │   ├── config-updates
    │   ├── external-driver
    │   ├── missing-modules
    │   ├── newrelease
    │   ├── sauce-patch
    │   └── upstream-patch
    ├── compat
    ├── control-scripts
    │   ├── headers-postinst
    │   ├── postinst
    │   ├── postinst.extra
    │   ├── postrm
    │   ├── postrm.extra
    │   ├── preinst
    │   └── prerm
    ├── debian.env
    ├── docs
    │   └── README.inclusion-list
    ├── gbp.conf
    ├── rules
    ├── rules.d
    │   ├── 0-common-vars.mk
    │   ├── 1-maintainer.mk
    │   ├── 2-binary-arch.mk
    │   ├── 3-binary-indep.mk
    │   ├── 4-checks.mk
    │   └── 5-udebs.mk
    ├── scripts
    │   ├── abi-check
    │   ├── config-check
    │   ├── control-create
    │   ├── link-headers
    │   ├── misc
    │   │   ├── getabis
    │   │   ├── git-ubuntu-log
    │   │   ├── insert-changes.pl
    │   │   ├── insert-mainline-changes
    │   │   ├── insert-ubuntu-changes
    │   │   ├── kernelconfig
    │   │   ├── retag
    │   │   └── splitconfig.pl
    │   ├── module-check
    │   ├── module-inclusion
    │   └── sub-flavour
    ├── source
    │   ├── format
    │   └── options
    ├── stamps
    │   └── keep-dir
    ├── tests
    │   ├── check-aliases
    │   └── README
    └── tools
        └── generic

Of these files, the following are part of a normal debian package, and I will ignore them for now:

    debian/compat

The `debian/rules` file is the other file that I recognize as being part of a normal debian package, but it is interesting because it is the primary makefile which performs the steps to build the deb.

I'm going to start there and walk through these files to figure out what they're doing.

## `debian/rules`

Looks like this loads the version from `debian/debian.env` (which in this case appears to be `debian.master` )

Inclues `debian/rules.d/0-common-vars.mk` which:

 * sets up variables for the ubuntu series, release, revisions, and previous revisions. Despite being a repo for precise, `series` is set to `oneiric`. Odd...
 * loads an environment file for the series (in this case, `debian/.oneiric-env`
 * runs some logic to determine whether to do build modules, abi checks, build debug modules, do a full build, and a few other flags that look like control WHAT is built.
 * Reads the kernel version, and generates variables that appear to hold the standard kernel package names ( `linux-image-$(abi\_release)` and friends )
 * Generates other package names for debug, tools, etc if enabled above.
 * Generates a kernel make command and stores it in the `kmake` variable.

Has `-include $(DEBIAN)/rules.d/$(arch).mk` which looks like it will include the architecture-specific file from `debian.master/rules.d/` if it exists.

For all of the architectures, this appears to set the following variables:

 * `human_arch`, `build_arch` and `header_arch`, which describe the architecture
 * `defconfig` appears to be a default config target to run to generate the default kernel config.
 * `flavours` appears to be a list of configurations to build the kernel in. Note that this is the British spelling of flavour.
 * `build_image` appears to be the name of the final kernel binary
 * `kernel_file` appears to the path to the kernel binary
 * `install_file` appears to be the name of the kernel binary when installed
 * `loader` appears to be the name of the bootloader
 * `no_dumpfile` appears to be true or false. not sure what this controls

Inclues `debian/rules.d/1-maintainer.mk`, which appears to be additional build targets to be used by the debian kernel maintainers.

Checks `DEB_STAGE` and appears to disable most targets for a `stage1` (?) build.

Defines a few basic targets.

Defines the `clean` target, which deletes a number of files from `debian` and copies their equivalents from `debian.master` into the `debian` directory.

Includes `debian/rules.d/2-binary-arch.mk`, which mostly seems to define rules to prepare the source tree for in-tree or out-of-tree builds, perform the build and some kind of build stamping action, and run install commands to copy built artifacts into the correct directory for their destination debian package. Most of this appears to be generic (make pattern rules) and is controlled by variables set in the previous inclusions. It looks like this also instantiates a copy of most of these rules for each flavour in the `flavours` variable.

Includes `debian/rules.d/5-udebs.mk`, which appears to be for building micro-debs. These are only used by the debian live CD and install CD, so I'm going to completely ignore this for now. If it looks like they're problematic later, it looks like they can be disabled by setting the `disable_d_i` variable to any value.

Includes the `debian/rules.d/3-binary-indep.mk` file, which appears to have the build rules for creating the kernel header, source and tools packages.

Includes `debian/rules.d/4-checks.mk`, which appears to have generic ABI and module check rules.

The main `debain/rules` then does some rather interesting things:

 * It generates `debian.master/control.stub` with a script that iterates over flavours (and sub-flavours?) and does some find and replace on `debian.master/control.stub.in` 
 * It generates `debian/control` from `debian.master/control.stub`, creates a bunch of build directories and files, and then runs a program called `kernel-wedge` to generate another control file in the build directory. Many of the inputs and flavour-specific files for this step appear to come from various places inside the `debian.master/d-i` directory.

It seems quite unusual to generate the `debian/control` file in this manner. This is likely a good part of the magic of this build and may deserve more study later.

## Other Files

 * `debian/commit-templates` do not appear to be reference from the rules. These are probably for manual use by the maintainers.
 * `debian/gpb.conf` is a configuration file for git-buildpackage. Hopefully can be ignored.
 * `debian/control-scripts` are used by 2-binary-arch.mk when assembling packages. Probably don't need to be modified.
 * `debian/docs` appears to contain some documentation justifying the existence of the module inclusion list. Since I don't plan to drastically change the structure of the packaging system, I probably don't need to read this.
 * `debian/scripts` look like scripts that are invoked from the package build rules.
 * `debian/source` files look like config files for the deb build system. Probably not something I'll need to change.
 * `debian/stamps` looks like some part of the build stamping process.
 * `debian/tests` appears to be a dedicated subsystem for running tests on built kernel package. It appears to contain a single test :P
 * `debian/tools/generic` appears to be a generic entrypoint for tools that are tied to the kernel version. It reads the name that it is invoked as, looks for an executable of the same name within a kernel-specific directory, and tries to invoke it. Clever, and almost certainly will not require modification.

## `debian.master`

Listing of `debian.master`:

    $ tree debian.master
    debian.master
    ├── abi
    │   ├── 3.2.0-125.168
    │   │   ├── abiname
    │   │   ├── amd64
    │   │   │   ├── generic
    │   │   │   ├── generic.compiler
    │   │   │   ├── generic.modules
    │   │   │   ├── virtual
    │   │   │   ├── virtual.compiler
    │   │   │   └── virtual.modules
    │   │   ├── armel
    │   │   │   ├── omap
    │   │   │   ├── omap.compiler
    │   │   │   └── omap.modules
    │   │   ├── armhf
    │   │   │   ├── highbank
    │   │   │   ├── highbank.compiler
    │   │   │   ├── highbank.modules
    │   │   │   ├── omap
    │   │   │   ├── omap.compiler
    │   │   │   └── omap.modules
    │   │   ├── fwinfo
    │   │   ├── i386
    │   │   │   ├── generic
    │   │   │   ├── generic.compiler
    │   │   │   ├── generic.modules
    │   │   │   ├── generic-pae
    │   │   │   ├── generic-pae.compiler
    │   │   │   ├── generic-pae.modules
    │   │   │   ├── virtual
    │   │   │   ├── virtual.compiler
    │   │   │   └── virtual.modules
    │   │   └── powerpc
    │   │       ├── powerpc64-smp
    │   │       ├── powerpc64-smp.compiler
    │   │       ├── powerpc64-smp.modules
    │   │       ├── powerpc-smp
    │   │       ├── powerpc-smp.compiler
    │   │       └── powerpc-smp.modules
    │   └── perm-blacklist
    ├── changelog
    ├── changelog.historical
    ├── config
    │   ├── amd64
    │   │   ├── config.common.amd64
    │   │   ├── config.flavour.generic
    │   │   └── config.flavour.virtual
    │   ├── armel
    │   │   ├── config.common.armel
    │   │   └── config.flavour.omap
    │   ├── armhf
    │   │   ├── config.common.armhf
    │   │   ├── config.flavour.highbank
    │   │   └── config.flavour.omap
    │   ├── config.common.ports
    │   ├── config.common.ubuntu
    │   ├── enforce
    │   ├── i386
    │   │   ├── config.common.i386
    │   │   ├── config.flavour.generic
    │   │   ├── config.flavour.generic-pae
    │   │   └── config.flavour.virtual
    │   ├── powerpc
    │   │   ├── config.common.powerpc
    │   │   ├── config.flavour.powerpc64-smp
    │   │   └── config.flavour.powerpc-smp
    │   └── ppc64
    │       ├── config.common.ppc64
    │       └── config.flavour.powerpc64-smp
    ├── control.d
    │   ├── flavour-control.stub
    │   ├── vars.generic
    │   ├── vars.generic-pae
    │   ├── vars.highbank
    │   ├── vars.omap
    │   ├── vars.powerpc64-smp
    │   ├── vars.powerpc-smp
    │   ├── vars.virtual
    │   └── virtual.inclusion-list
    ├── control.stub.in
    ├── copyright
    ├── deviations.txt
    ├── d-i
    │   ├── exclude-firmware.armel-omap
    │   ├── exclude-firmware.armhf-highbank
    │   ├── exclude-firmware.armhf-omap
    │   ├── exclude-modules.amd64-virtual
    │   ├── exclude-modules.armel-omap
    │   ├── exclude-modules.armhf-highbank
    │   ├── exclude-modules.armhf-omap
    │   ├── exclude-modules.i386-virtual
    │   ├── exclude-modules.ia64
    │   ├── exclude-modules.powerpc
    │   ├── exclude-modules.sparc
    │   ├── firmware
    │   │   ├── nic-modules
    │   │   ├── README.txt
    │   │   └── scsi-modules
    │   ├── kernel-versions.in
    │   ├── modules
    │   │   ├── block-modules
    │   │   ├── crypto-modules
    │   │   ├── fat-modules
    │   │   ├── fb-modules
    │   │   ├── firewire-core-modules
    │   │   ├── floppy-modules
    │   │   ├── fs-core-modules
    │   │   ├── fs-secondary-modules
    │   │   ├── input-modules
    │   │   ├── ipmi-modules
    │   │   ├── irda-modules
    │   │   ├── md-modules
    │   │   ├── message-modules
    │   │   ├── mouse-modules
    │   │   ├── multipath-modules
    │   │   ├── nfs-modules
    │   │   ├── nic-modules
    │   │   ├── nic-pcmcia-modules
    │   │   ├── nic-shared-modules
    │   │   ├── nic-usb-modules
    │   │   ├── parport-modules
    │   │   ├── pata-modules
    │   │   ├── pcmcia-modules
    │   │   ├── pcmcia-storage-modules
    │   │   ├── plip-modules
    │   │   ├── ppp-modules
    │   │   ├── sata-modules
    │   │   ├── scsi-modules
    │   │   ├── serial-modules
    │   │   ├── speakup-modules
    │   │   ├── squashfs-modules
    │   │   ├── storage-core-modules
    │   │   ├── usb-modules
    │   │   ├── virtio-modules
    │   │   └── vlan-modules
    │   ├── modules-powerpc
    │   │   ├── block-modules
    │   │   ├── message-modules
    │   │   ├── nic-modules
    │   │   ├── scsi-modules
    │   │   └── storage-core-modules
    │   ├── modules-sparc
    │   │   ├── block-modules
    │   │   └── message-modules
    │   └── package-list
    ├── etc
    │   ├── getabis
    │   └── kernelconfig
    ├── NOTES
    └── rules.d
        ├── amd64.mk
        ├── armel.mk
        ├── armhf.mk
        ├── i386.mk
        ├── powerpc.mk
        └── ppc64.mk

Off the top, a few of these directories and files look familiar from looking through previous rules:

 * `debian.master/abi` ABI symbol definitions for kernel variants. I think these are checked in to make sure that new kernels are ABI compatible with previous builds of the same kernel.
 * `debian.master/changelog` and `debian.master/changelog.historical`: the standard debian package changelog.
 * `debian.master/config` directory appears to contain common and flavor-specific configs. I suspect this is where the majority of the work will happen.
 * `debian.master/config/enforce` appears to be some kind of validation that every flavor has some config options set. `debian/scripts/config-check` appears to use this file.
 * `debian.master/control.d` appear to be debian control file chunks that are included for each kernel flavor.
 * `debian.master/control.stub.in` is the template for the control file; covered in previous section.
 * `debian.master/copyright` copyright. Read it, respect it.
 * `debian.master/deviations.txt` appears to be a partial list of changes. Not sure what these are against...
 * `debian.master/d-i` I'll come back to this one.
 * `debian.master/etc` scripts appear to be used by `debian/scripts/misc`. Not quire sure what these do, but they feel old and maybe a little crusty.
 * `debian.master/NOTES` someone left their notes here. Seems to be mostly notes about which patches are applied to this kernel.
 * `debian.master/rules.d` arch-specific build rules; see `debian.rules` section above.

## `debian.master/d-i`

I said I'd come back to it!

This directory contains the `kernel-versions.in` file which seems to contain metadata about the arch/flavour combinations, covered above when going through `debian/rules`

The rest of this directory seems to contain files that map kernel modules to modules packages, and blacklists for some of those modules packages on some arch/flavour combinations. This looks completely unfamiliar to me; I don't think these packages are part of my current Ubuntu distribution.

DOH! I've been looking at the kernel build system for Ubuntu Precise (12.04), released in 2012! No wonder these flavors look a little old.

# Switch gears to Ubuntu 14.04 and 18.04

Ok; now checking out the 14.04 kernel to see how it has evolved:

    git clone git://kernel.ubuntu.com/ubuntu/ubuntu-trusty.git

The `debian` directory and `debian/rules` appear largely unchanged. There are a few more scripts in `debian/scripts`, but the overall structure appears unchanged.

The `debian.master` directory has substaintially more flavours, but again appears otherwise unchanged.

Comparing with 18.04 ( `git://kernel.ubuntu.com/ubuntu/ubuntu-bionic.git` ), most of the structure again appears the same, but the structure of `debian.master/d-i/firmware` and `debian.master/d-i/modules` appears to have a bunch of new sub-directories for each architecture.

# The Transplant

Now that I have the lay of the land, I think it's time to try transplanting these changes onto my kernel and attempting a build.

I copied over all of `debian` and `debian.master` from ubuntu's kernel into my kernel tree. Since I'm only focusing on an amd64 architecture, I removed the files for all of the other architectures. I now have the following directory structure alongside the rest of the kernel source:

    debian
    ├── cloud-tools
    │   ├── hv_get_dhcp_info
    │   ├── hv_get_dns_info
    │   └── hv_set_ifconfig
    ├── commit-templates
    │   ├── bumpabi
    │   ├── config-updates
    │   ├── external-driver
    │   ├── missing-modules
    │   ├── newrelease
    │   ├── sauce-patch
    │   └── upstream-patch
    ├── compat
    ├── control.d
    │   └── flavour-buildinfo.stub
    ├── debian.env
    ├── docs
    │   └── README.inclusion-list
    ├── gbp.conf
    ├── linux-cloud-tools-common.hv-fcopy-daemon.upstart
    ├── linux-cloud-tools-common.hv-kvp-daemon.upstart
    ├── linux-cloud-tools-common.hv-vss-daemon.upstart
    ├── rules
    ├── rules.d
    │   ├── 0-common-vars.mk
    │   ├── 1-maintainer.mk
    │   ├── 2-binary-arch.mk
    │   ├── 3-binary-indep.mk
    │   ├── 4-checks.mk
    │   └── 5-udebs.mk
    ├── scripts
    │   ├── abi-check
    │   ├── config-check
    │   ├── control-create
    │   ├── helpers
    │   │   ├── close
    │   │   ├── open
    │   │   └── rebase
    │   ├── link-headers
    │   ├── misc
    │   │   ├── final-checks
    │   │   ├── find-obsolete-firmware
    │   │   ├── fw-to-ihex.sh
    │   │   ├── gen-auto-reconstruct
    │   │   ├── getabis
    │   │   ├── get-firmware
    │   │   ├── git-ubuntu-log
    │   │   ├── insert-changes.pl
    │   │   ├── insert-mainline-changes
    │   │   ├── insert-ubuntu-changes
    │   │   ├── kernelconfig
    │   │   ├── retag
    │   │   └── splitconfig.pl
    │   ├── module-check
    │   ├── module-inclusion
    │   ├── retpoline-check
    │   ├── retpoline-extract
    │   ├── retpoline-extract-one
    │   └── sub-flavour
    ├── source
    │   ├── format
    │   └── options
    ├── stamps
    │   └── keep-dir
    ├── templates
    │   ├── extra.postinst.in
    │   ├── extra.postrm.in
    │   ├── headers.postinst.in
    │   ├── image.postinst.in
    │   ├── image.postrm.in
    │   ├── image.preinst.in
    │   └── image.prerm.in
    ├── tests
    │   ├── control
    │   ├── rebuild
    │   └── ubuntu-regression-suite
    ├── tests-build
    │   ├── check-aliases
    │   └── README
    └── tools
        └── generic
    debian.master
    ├── abi
    │   └── perm-blacklist
    ├── changelog
    ├── changelog.historical
    ├── config
    │   ├── amd64
    │   │   ├── config.common.amd64
    │   │   ├── config.flavour.generic
    │   │   └── config.flavour.lowlatency
    │   ├── annotations
    │   ├── config.common.ports
    │   ├── config.common.ubuntu
    │   └── enforce
    ├── control.d
    │   ├── flavour-control.stub
    │   ├── generic.inclusion-list
    │   ├── vars.generic
    │   └── vars.lowlatency
    ├── control.stub.in
    ├── copyright
    ├── d-i
    │   ├── exclude-modules.amd64-virtual
    │   ├── firmware
    │   │   ├── kernel-image
    │   │   ├── nic-modules
    │   │   ├── README.txt
    │   │   └── scsi-modules
    │   ├── kernel-versions.in
    │   ├── modules
    │   │   ├── block-modules
    │   │   ├── crypto-modules
    │   │   ├── fat-modules
    │   │   ├── fb-modules
    │   │   ├── firewire-core-modules
    │   │   ├── floppy-modules
    │   │   ├── fs-core-modules
    │   │   ├── fs-secondary-modules
    │   │   ├── input-modules
    │   │   ├── ipmi-modules
    │   │   ├── irda-modules
    │   │   ├── kernel-image
    │   │   ├── md-modules
    │   │   ├── message-modules
    │   │   ├── mouse-modules
    │   │   ├── multipath-modules
    │   │   ├── nfs-modules
    │   │   ├── nic-modules
    │   │   ├── nic-pcmcia-modules
    │   │   ├── nic-shared-modules
    │   │   ├── nic-usb-modules
    │   │   ├── parport-modules
    │   │   ├── pata-modules
    │   │   ├── pcmcia-modules
    │   │   ├── pcmcia-storage-modules
    │   │   ├── plip-modules
    │   │   ├── ppp-modules
    │   │   ├── sata-modules
    │   │   ├── scsi-modules
    │   │   ├── serial-modules
    │   │   ├── speakup-modules
    │   │   ├── squashfs-modules
    │   │   ├── storage-core-modules
    │   │   ├── usb-modules
    │   │   ├── virtio-modules
    │   │   └── vlan-modules
    │   └── package-list
    ├── etc
    │   ├── getabis
    │   └── kernelconfig
    ├── NOTES
    ├── reconstruct
    ├── rules.d
    │   └── amd64.mk
    └── tracking-bug

As a quick check that things are not completely insane, I ran `fakeroot ./debian/rules clean` and was pleasantly surprised to find that it succeeded.

Since I'm only trying to build one flavor (my custom flavour), I made the following hacks:
 * Modified `debian.master/rules.d/amd64.mk` to list only that flavour in the `flavours`
 * Removed the contents of `debian.master/config/config.common.ubuntu` and `debian.master/config/amd64/config.common.amd64`
 * Deleted `debian.master/config/amd64/config.flavour.generic` and `debian.master/config/amd64/config.flavour.lowlatency`
 * Created `debian.master/config/amd64/config.flavour.my-flavour` with my entire kernel config.

At this point, I _should_ be able to run the build and get all the packages out of it. Let's see if it works...

    CONCURRENCY_LEVEL=24 fakeroot ./debian/rules binary

(yes, I have a 24-core machine. Set `CONCURRENCY_LEVEL` appropriately for your computer)

Unsurprisingly, I'm not quite that lucky:

    /home/austin/linux/scripts/Makefile.headersinst:63: *** Some mandatory headers (poll.h) are missing in arch/x86/include/uapi/asm.  Stop.
    make[3]: *** [asm] Error 2
    make[2]: *** [headers_install] Error 2
    make[2]: Leaving directory `/home/austin/linux/debian/tmp-headers'
    make[1]: *** [sub-make] Error 2
    make[1]: Leaving directory `/home/austin/linux'
    make: *** [install-arch-headers] Error 2

Also of note, the version reported during the build is completely wrong. Adding the appropriate entry to `debian.master/changelog` quickly fixes that.

It looks like the missing `poll.h` header has moved from `arch/x86/include/uapi/asm/poll.h` to `arch/x86/include/generated/uapi/asm/poll.h` as some point. It looks like the Bionic `hwe-edge` branch currently has a similar kernel version and some build support for that, so I'll try to port that in.

... hours pass ...

I finally tracked down the build issue. It seems to be some kind of issue if the local kernel build tree is not completely clean. I removed all of the generated headers, and the build seems to be past that step. 

I encountered a few other errors that were also resolved by cleaning my source directory (I had been doing normal builds in it before I added the debian rules). `make mrproper` will clean out EVERYTHING, but unfortunately this also includes the `debian` directory that I'm working on. Luckily I habitually check things into git so all I had to do to recover was a git checkout. I also added this patch to skip that step in the future (I'm on a 4.19.2 kernel. YMMV):

    diff --git a/scripts/package/Makefile b/scripts/package/Makefile
    index 73503ebce632..d96ac1649aea 100644
    --- a/scripts/package/Makefile
    +++ b/scripts/package/Makefile
    @@ -82,7 +82,7 @@ bindeb-pkg: FORCE
     intdeb-pkg: FORCE
            +$(CONFIG_SHELL) $(srctree)/scripts/package/builddeb
     
    -clean-dirs += $(objtree)/debian/
    +#clean-dirs += $(objtree)/debian/
     
     # snap-pkg
     # ---------------------------------------------------------------------------

The config/enforce step also found a few options that I should enable, so I used `fakeroot ./debian/rules editconfigs` to edit my flavours and enable those configs. (The search operator, `/`, in menuconfig makes finding and enabling the missing options easy). This also moved all of my config options from my flavour to the common options, but that seems like the desired behavior so I left it. If I find myself needing to add move flavours later, this is probably the right path to take.

... building again ... more hours pass ...

Modules packaging error because I had `CONFIG_LOCALVERSION` set. Reset it to the empty string in my config.

... building ... minutes pass ...

Module check failed:

    Debug: module-check-my_flavour
    II: Checking modules for my_flavour...     prev_abidir : /home/austin/linux/debian.master/abi/4.19.2-0.0/amd64previous or current modules file missing!
       /home/austin/linux/debian.master/abi/4.19.2-1/amd64/my_flavour.modules
       /home/austin/linux/debian.master/abi/4.19.2-0.0/amd64/my_flavour.modules

Really, since I'm bootstrapping a branch new kernel, I don't have a previous ABI version to check against so I'd like to either disable this check or bootstrap it with itself.

It looks like this check can be skipped by adding an ignore file for the "previous" version:

    $ mkdir -p debian.master/abi/4.19.2-0.0/amd64
    $ touch debian.master/abi/4.19.2-0.0/amd64/ignore

I had to add similar ignores for modules:

    $ touch debian.master/abi/4.19.2-0.0/amd64/ignore.modules

Finally, the retpoline check also failed for a similar reason, so I fixed it by duplicating the new retpoline to the "previous" version:

    $ cp debian.master/abi/4.19.2-1/amd64/my_flavour.retpoline debian.master/abi/4.19.2-0.0/amd64/

... more time passes ...

Another stray build error appears:

    dpkg-gencontrol: error: package linux-image-unsigned-4.19.2-1-my_flavour not in control info
    dh_gencontrol: dpkg-gencontrol -plinux-image-unsigned-4.19.2-1-my_flavour -ldebian/changelog -Tdebian/linux-image-unsigned-4.19.2-1-my_flavour.substvars -Pdebian/linux-image-unsigned-4.19.2-1-my_flavour -Vlinux:rprovides= returned exit code 255
    make: *** [binary-my_flavour] Error 25

Looks like this is due to the `debian/control` file not being generated correctly; it still has entries for the original `generic` and `lowlatecy` flavours, instead of `my_flavour`.

Removed the old lowlatency control variables and renamed the other one to my flavour:

    rm debian.master/control.d/vars.lowlatency
    mv debian.master/control.d/vars.generic debian.master/control.d/vars.my_flavour

`debian.master/d-i/kernel-versions.in` also needed an update, to change the flavour from `generic` to `my_flavour`.

Luckily, these changes are easy to verify by running `fakeroot ./debian/rules clean` and then inspecting the resulting `debian/control` file.

... another interminable wait for the build ...

Further progress; now `Debug: binary-udebs` fails:

    find: `debian/serial-modules-4.19.2-1-my_flavour-di': No such file or directory
    serial-modules-4.19.2-1-my_flavour-di will be empty
    find: `debian/ppp-modules-4.19.2-1-my_flavour-di': No such file or directory
    ppp-modules-4.19.2-1-my_flavour-di will be empty
    find: `debian/firewire-core-modules-4.19.2-1-my_flavour-di': No such file or directory
    firewire-core-modules-4.19.2-1-my_flavour-di will be empty
    find: `debian/plip-modules-4.19.2-1-my_flavour-di': No such file or directory
    plip-modules-4.19.2-1-my_flavour-di will be empty
    find: `debian/pcmcia-storage-modules-4.19.2-1-my_flavour-di': No such file or directory
    pcmcia-storage-modules-4.19.2-1-my_flavour-di will be empty
    find: `debian/irda-modules-4.19.2-1-my_flavour-di': No such file or directory
    irda-modules-4.19.2-1-my_flavour-di will be empty
    find: `debian/parport-modules-4.19.2-1-my_flavour-di': No such file or directory
    parport-modules-4.19.2-1-my_flavour-di will be empty
    find: `debian/nic-pcmcia-modules-4.19.2-1-my_flavour-di': No such file or directory
    nic-pcmcia-modules-4.19.2-1-my_flavour-di will be empty
    find: `debian/pcmcia-modules-4.19.2-1-my_flavour-di': No such file or directory
    pcmcia-modules-4.19.2-1-my_flavour-di will be empty
    find: `debian/squashfs-modules-4.19.2-1-my_flavour-di': No such file or directory
    squashfs-modules-4.19.2-1-my_flavour-di will be empty
    find: `debian/speakup-modules-4.19.2-1-my_flavour-di': No such file or directory
    speakup-modules-4.19.2-1-my_flavour-di will be empty
    find: `debian/fs-core-modules-4.19.2-1-my_flavour-di': No such file or directory
    fs-core-modules-4.19.2-1-my_flavour-di will be empty
    make[1]: *** [do-binary-udebs] Error 1

Looks like most of those are due to my choice of kernel config. I think I can suppress them by listing those modules (or module groups?) in `debian.master/d-i/exclude-modules.amd64-my_flavour`

... build continues ...
