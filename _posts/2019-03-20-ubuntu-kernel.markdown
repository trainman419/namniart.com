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
