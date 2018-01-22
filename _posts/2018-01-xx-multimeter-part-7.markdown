---
layout: post
title: "Nixie MultiMeter, Part 6"
excerpt: Building a Nixie MultiMeter, Part 6
categories:
- Electronics
- PCB
---

## Selecting Components for the Resistor Ladder

I'm looking for surface mount resistors with 0.1% tolerance for the voltage divider ladder described in the last post.

Using high-precision resistors means that I don't need to worry as much about how the resistor tolerances will affect the input stage, and I may even be able to avoid calibration if I choose my components carefully.

Unfortunately I'm not able to find any 9.09M, 0.1% resistors on digikey, so I need to go back and choose a slightly different resistance to achieve the desired tolerance. According to [this OhmCraft white paper](https://www.ohmcraft.com/uploads/WP_HighVoltageChipResistors.pdf), resistor size is one of the primary influencing factors of voltage tolerance, and 2512 size and up are typically tolerant of 2.5kV, so I'll start my search with 1/3 watt, 2512 and larger 0.1% resistors.

The [Stackpole Electronics HVCB2010BDE10M0](https://www.digikey.com/product-detail/en/stackpole-electronics-inc/HVCB2010BDE10M0/HVCB2010BDE10M0CT-ND/5824174) is a 10M resistor that meets all of these specifications, but it's $13. I may need to look for a through-hole version or rethink some part of this design.

The [Caddock Electronics USF370-10.0M-0.1%-5PPM](https://www.digikey.com/product-detail/en/caddock-electronics-inc/USF370-10.0M-0.1-5PPM/USF370-10.0M-B-5PPM-ND/4360712) is a 10M through-hole resistor that meets the resistance and wattage specification, but only goes to 1.4kV, and it's $25! I really need to rethink something in my design if it's this difficult to find parts.