---
layout: post
title: "Nixie MultiMeter, Part 16"
excerpt: 
categories:
- Electronics
- PCB
---

## Design Completion Checklist

I'm coming up on the end of this project; partly because I'm close to having the schematic complete, and partly because I'm ready to be done. I've put together a checklist of the things that I need and want to get done before I start doing PCB layout.

 * Must Have
    * [ ] Higher-power op-amps for resistance drive and virtual ground
    * [ ] Prevent floating leads/inputs in 4-wire mode
    * [ ] LEDs for decimal point, range and mode indication
    * [ ] 3.3V regulator
    * [ ] Power input jack
    * [ ] Power supply decoupling for MSP430
    * [ ] Power supply decoupling for op-amps
    * [ ] MSP430 programming and debug connector (and cable?)
    * [ ] MSP430 crystal
    * [ ] Inter-board connector or cable
    * [ ] Mode select method (switches, buttons, rotary switch?)
 * Optional
    * [ ] Resistance Calibration Mode(s)
    * [ ] Try to reduce number of relays to reduce cost
    * [ ] Look for other easy ways to reduce cost.
    * [ ] Computer uplink for automated measurement (Serial, USB, or other)
    * [ ] 3D models for the remaining components that do not currently have models. Not a big deal for small components, but will help verify footprints on more complex items and help visualize the soldering and assembly process.