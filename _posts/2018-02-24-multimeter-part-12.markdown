---
layout: post
title: "Nixie MultiMeter, Part 12"
excerpt: Nixie Drive circuit
categories:
- Electronics
- PCB
---

Now that I have my input stages mostly complete and my microcontroller chosen, it's time to figure out how to drive my nixie tubes.

I have some basic constraints and assumptions from the rest of the circuit:

 * 12V supply for relays, 3.3V supply for microcontroller, 160V supply for nixies.
 * 56 available I/O pins on my microcontroller of choice. A few of these have unusual names like RST or TCK and will probably be needed for a JTAG interface. It looks like there are **48 pins** that are not spoken for at all. If I need more than 48, I'll need to study the alternate functions on the datasheet to make sure I'm not accidentally retasking an important JTAG or reset pin.
 * 4 Nixie digits, which require a total of **40 control lines** at 1.5mA
 * Minus sign and 3 or 4 decimal points (and possibly other ways of indicating the range selection; maybe **6 LEDs total**, maybe 20mA)
 * Mode selection switch for selecting Voltage, Current, Ohms and Power modes (**4 modes in total**).
 * Maybe some additional buttons or inputs for changing the range selection (**2 or 3 buttons?**).
 * **10 relays** in the current and voltage selection stages, and maybe a few more for Ohms range selection. (12mA to 67mA)

In total, it looks like I'll need about 40+6+10 = 56 outputs and 4+3 = 7 inputs for a total of 63 control lines. This is more pins than I have on the microcontroller I've selected, so I'll probably need to control at least a few of these with input or output multiplexers.

I'm not a big fan of input multiplexers, they tend to be slow, and all of my digital inputs are buttons, so I'm going to plan to run all of my inputs directly to my microcontroller.

That leaves 41 pins (or maybe a few more) to drive 56 outputs, even before I need to figure out how I'm going to drive hundreds of volts from a 3V microcontroller.

# Nixie Drive Circuit

Some quick searching on Google turns up the following articles about how to drive nixie tubes. I'll quickly sum up each one:

### [http://www.glowbug.nl/neon/HowToDriveNixies.html](http://www.glowbug.nl/neon/HowToDriveNixies.html)

Discusses high-voltage, discrete transistors, purpose-built chips like the 74141 and 7441, and using other, more modern components like the SN75468 or ULN2003 that aren't explicitly designed for driving Nixie tubes, but which have clamping diodes on the output.

### [http://www.neonixie.com/ic/index.html](http://www.neonixie.com/ic/index.html)

Sells a Russian variant or clone of the 74141 chip for driving nixie tubes. [About $4 each.](http://www.allspectrum.com/store/74141-nixie-tube-driver-ic-russian-p-211.html)

### [http://electronbunker.ca/eb/Nixie.html](http://electronbunker.ca/eb/Nixie.html)

Dicusses a nixie driver using the A6818 or the MAX6922, which were originally designed for driving vacuum fluorescent displays.

### [https://idyl.io/arduino/how-to/control-a-nixie-tube-with-arduino/](https://idyl.io/arduino/how-to/control-a-nixie-tube-with-arduino/)

Discusses how to control a nixie with an arduino, using the 74141 or one of its variants.

### [https://web.jfet.org/nixie-1/NixieTransistors.pdf](https://web.jfet.org/nixie-1/NixieTransistors.pdf)

Discusses how to drive nixie tubes with discrete transistors, and discusses a number of multiplexing methods for reducing the number of control pins required per output.

### [https://threeneurons.wordpress.com/nixie-power-supply/](https://threeneurons.wordpress.com/nixie-power-supply/)

Discusses a number of things that can go wrong when driving nixie tubes, such as blue dots and ghosting of the other digits, and how to avoid them. Also spends a lot of time talking about clocks for some reason.

### [http://www.electricstuff.co.uk/nixclock.html](http://www.electricstuff.co.uk/nixclock.html)

An interesting circuit from a simpler time, this design generates the driving voltage for the nixie tubes by rectifying 220V AC (or rectifying and doubling 110V!), and then drives the cathode side of the nixie tube with discrete transistors. There's not actually a lot of detail here about driving nixie tubes; I just love the simplicity of this circuit.

### [https://electronics.stackexchange.com/questions/335731/when-driving-a-nixie-tube-with-the-hv5622-shift-register-how-hot-will-the-chip?rq=1](https://electronics.stackexchange.com/questions/335731/when-driving-a-nixie-tube-with-the-hv5622-shift-register-how-hot-will-the-chip?rq=1)

Asks about driving a nixie tube with a HV5622 chip; notable for it's mention of this chip, which seems to be an open-drain shift register with an output voltage rating of over 200V! It also seems to be stocked by DigiKey as I write this. [http://reboots.g-cipher.net/time/](http://reboots.g-cipher.net/time/) also discusses a Nixie clock design based on the HV5622.

### [https://twitter.com/surfncircuits/status/965470697223958528](https://twitter.com/surfncircuits/status/965470697223958528)

Mark Smith, whose 160V boost supply design I've borrowed, is using the TPIC6B595 to drive his nixie tubes. It's an 8-bit shift register with open-drain outputs and integrated voltage clamps.

# Technique Summary

## Discrete Transistors

Discrete transistors are easy to understand and select; you just use one transistor for each numeral, and choose a trasistor with a voltage rating above the drive voltage. If I multiplex these I may be able to drive the 10 outputs for each digit with 6 or maybe as few as 4 pins.
Using discrete transistors with a high voltage rating and low leakage means that the cathode voltage can float all the way to the full drive voltage, and this will prevent unlit digits from glowing dimly or generating the blue dot pheonmena.

## 74141 and other clamped driver ICs

The 74141, the Russian 7441 variant, and the TPIC6B595 all have open-drain outputs with voltage clamps around 60V, and the designs that use an ULN2003 use an external voltage clamp to clamp the voltage around 50V. Most of these have the advantage that they integrate a shift register or BCD decoder and driver into a single chip, so they need far fewer control pins. The disadvantage is that they all appear to be 5V TTL components, which means that they have relatively high idle power draw. Since TTL components don't work well outside of their specified voltage range and have relatively stringent input voltage thresholds for 1 and 0, these will require a separate 5V supply and level shifting circuitry. The clamp diode in these circuits also conducts some current even when the transistor is off, which may prevent the digit from extinguishing, may cause some extra glow when other digits are illuminated, and may result in a lot of extra glow when all of the digits are off.

## VFD (Vacuum Flourescent Display) driver ICs

These VFD driver ICs are more interesting because the logic side operates at 3.3V, but they also require a 70 to 80V supply. When the output is off the output voltage is limited to their supply voltage, so these will likely have some of the same extra glow issues that can be seen with the solutions that use clamping diodes. Due to the higher voltage these are less likely to have issues extinguishing digits.
Since this is a CMOS component, the supply current is significantly lower than the 74141 and it's TTL companions.

## HV5622

The [HV5622](http://ww1.microchip.com/downloads/en/DeviceDoc/20005854A.pdf) is a 32-output, 12V shift register with open-drain outputs rated to 220V. Since it doesn't have clamping diodes, there's less worry about extra glow, and no worries about its ability to extinguish the nixie digits when the output turns off.
The downside is that the logic side of this chip operates at 12, which will require some level shifting. Fortunately, I'm already planning to have a 12V supply, and since it's a shift register and I can daisy-chain it, I'll only need to level-shift a few bits to drive all of my outputs.
While the operating supply voltage for these is about 10-14V, the second design linked above and other designs online operate them at 5V. The voltage thresholds for a logic 1 and 0 are 2V from Vcc and GND respectively, so that gives some explanation of why these still function at 5V, but I suspect they would not operate well below 4V.
Running these at 5V also reduces the possible gate drive voltage for the output transistors, which probably increase their on-state resistance and therefore reduces the maximum current that they can drive.
Based on the datasheet's maximum voltage of 15V when the output is on and passing 100mA, these probably have a maximum output resitance of about 150 Ohms.

# Overall Summary

Given the supply voltages that I already have available, the need to multiplex the outputs and my desire to avoid problems with the nixie tubes, I'm going to base my design off the HV5622. Since it can sink at least 100mA, it may also be able to drive my display LEDs and range selection relays, and since I'll probably be chaining two together but I only need 40 of those outputs for the nixie tubes, I'll have an extra 24 outputs that I can use for relays, LEDs and other output devices.