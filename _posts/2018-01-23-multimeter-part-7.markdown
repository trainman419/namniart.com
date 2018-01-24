---
layout: post
title: "Nixie MultiMeter, Part 7"
excerpt: Building a Nixie MultiMeter, Part 7
categories:
- Electronics
- PCB
---

## Selecting Components for the Resistor Ladder

I'm looking for surface mount resistors with 0.1% tolerance for the voltage divider ladder described in the last post.

Using high-precision resistors means that I don't need to worry as much about how the resistor tolerances will affect the input stage, and I may even be able to avoid calibration if I choose my components carefully.

### 9.09M Resistor (top of ladder)

Unfortunately I'm not able to find any 9.09M, 0.1% resistors on digikey, so I need to go back and choose a slightly different resistance to achieve the desired tolerance. According to [this OhmCraft white paper](https://www.ohmcraft.com/uploads/WP_HighVoltageChipResistors.pdf), resistor size is one of the primary influencing factors of voltage tolerance, and 2512 size and up are typically tolerant of 2.5kV, so I'll start my search with 1/3 watt, 2512 and larger 0.1% resistors.

The [Stackpole Electronics HVCB2010BDE10M0](https://www.digikey.com/product-detail/en/stackpole-electronics-inc/HVCB2010BDE10M0/HVCB2010BDE10M0CT-ND/5824174) is a 10M resistor that meets all of these specifications, but it's $13. I may need to look for a through-hole version or rethink some part of this design.

The [Caddock Electronics USF370-10.0M-0.1%-5PPM](https://www.digikey.com/product-detail/en/caddock-electronics-inc/USF370-10.0M-0.1-5PPM/USF370-10.0M-B-5PPM-ND/4360712) is a 10M through-hole resistor that meets the resistance and wattage specification, but only goes to 1.4kV, and it's $25! I really need to rethink something in my design if it's this difficult to find parts.

The first obvious thing to reconsider is the input impedance; selecting a 1M input impedance suggests a resistor in the 909k to 1M range, still subject to 1.8kV at maximum rated voltage. Some searching on DigiKey doesn't find any suitable resistors in that resistance range either.

It seems like either I'm making some fundamental error in choosing this resistor, or it's just that much more expensive than I was expecting. EEVblog has [a nice review](https://www.eevblog.com/forum/blog/eevblog-373-multimeter-input-protection-tutorial/)

<iframe width="560" height="315" src="https://www.youtube.com/embed/zUhnGp5vh60" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>

The biggest takeaway here seems to be that even the professional multimeters are using the more expensive, precision resistors for their input protection.

Following the video above, the other good reference here may be [Dave Jones' 121GW Multimeter](https://www.kickstarter.com/projects/eevblog/eevblog-121gw-multimeter), but of course it looks like the schematic isn't available.

With all of those things in mind, I'm going to go with the [Stackpole Electronics HVCB2010BDE10M0](https://www.digikey.com/product-detail/en/stackpole-electronics-inc/HVCB2010BDE10M0/HVCB2010BDE10M0CT-ND/5824174) 10M resistor. The extra $13 is worth the safety and I'd rather not compromise on the other aspects of the design.

This means that all of the resistors in the ladder need to increase to multiples of 10 instead of multiples of 9.09.

### 1M Resistor (2nd in ladder)

A DigiKey search quickly turns up a number of inexpensive 0.1%, 1M resistors. The least expensive are the 0805 (size) [RT series](http://www.yageo.com/documents/recent/PYu-RT_1-to-0.01_RoHS_L_9.pdf), but they're only rated to 150V, so I chose a 1M, 1206 resistor that is rated to 200V, the [Yaego RT1206BRE071ML](https://www.digikey.com/product-detail/en/yageo/RT1206BRE071ML/YAG5104CT-ND/6617260). This is a 0.25W resistor which vastly exceeds my original 0.036W spec.

### 100k, 10k, 1k and 111 Ohm resistors (remainder of ladder)

The remaining resistors have a maximum expected voltage of about 20V, which is within the working voltage spec for even the tiny RT series 0201 resistors, so I'm just going to pick 0603 resistors because that's a common size, and they're not too small to solder by hand.

 * 100k: [Yaego RT0805BRE07100KL](https://www.digikey.com/product-detail/en/yageo/RT0805BRE07100KL/YAG2326CT-ND/5252440)
 * 10k: [Yaego RT0603BRE0710KL](https://www.digikey.com/product-detail/en/yageo/RT0603BRE0710KL/YAG2313CT-ND/5252427)
 * 1k: [Yaego RT0603BRE071KL](https://www.digikey.com/product-detail/en/yageo/RT0603BRE071KL/YAG2314CT-ND/5252428)

The only 111 Ohm, 0.1% resistor is the [Yaego TNPW0402111RBEED](https://www.digikey.com/product-detail/en/vishay-dale/TNPW0402111RBEED/541-2065-1-ND/4876030). It's an 0402 package, so it's a bit small, but I've soldered 0402 parts by hand before and it fits the rest of the specs, so I guess I'll have to live with it.
