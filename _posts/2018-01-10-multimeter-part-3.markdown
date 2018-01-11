---
layout: post
title: "Nixie MultiMeter, Part 3"
excerpt: Building a Nixie MultiMeter, Part 3
categories:
- Electronics
- PCB
---

After yesterday's datasheet adventure, I'm going to go back to looking at the multimeter ADC and frontend today.

I'm going to start by further narrowing down the different basic design options.

 * Using one of the existing designs from The Art of Electronics:
   * This seems very overkill for this project. I also don't think I have the time or the skill to calibrate an instrument that sensitive, and I'm not going to enjoy the project less if I only have a 3 or 4-digit multimeter when I'm done.
 * Dedicated Multimeter IC:
   * I did a search for these on DigiKey, and found that they have a category for [Data Acquisition - ADCs/DACs - Special Purpose](https://www.digikey.com/products/en/integrated-circuits-ics/data-acquisition-adcs-dacs-special-purpose/768).
   * Most of these are 3.5, 3.75 or 4.5 digit components that roughly match the accuracy that I'm going for, but many of them are designed to drive the segments on a seven-segment LED or LCD display, and will not be easy to interface with Nixie tubes.
   * A few, like the [ICL7135](https://www.intersil.com/content/dam/Intersil/documents/icl7/icl7135.pdf), look promising because they have BCD (Binary Coded Decimal) outputs, but even this will be tricky to interface with Nixie tubes because it expects to be driving the anodes too, and that may be difficult.
   * BCD Nixie driver ICs seem pretty pricey compared to similar BJTs, so there isn't an advantage to using the BCD output.
   * The number of digits on these chips doesn't match the number of digits on my display, so either the high numbers on the upper digit will be unused (for a 3.5 digit IC on a 4-digit display) or the upper output digit will have to be discarded (for a 3.5 digit IC on a 4-digit display). This isn't a big deal but it seems like a shame.
   * Using a completely off-the-shelf chip also limits the final multimeter to the functions provided by that chip. If I want to do something else, like signal postprocessing, a computer connection, or just change the functionality later, that will be difficult.
 * DAC and Microcontroller:
   * I haven't done any additional research here yet, but DACs, microcontrollers and individual BJTs are easy to get, so I expect this to be possible even if it's a little more involved than using more specialized parts.
 * Existing reference designs:
   * All of the existing reference designs I found in [Part 1](2018-01-01-multimeter-part-1.html) use a dedicated multimeter IC, so this is equivalent to the second option.

At this point, it seems like it's down to two options: deal with the quirks of interfacing a multimeter IC with Nixie tubes, or take on the challenge of doing everything myself with just an ADC and a multimeter.

I'm going to go through the reference designs in more detail to get a better understanding of the analog side of the multimeter ICs, to get a better appreciation for them and see what I'll have to take on if I choose to go my own route.

## [ADD3501 Reference Design](http://www.ti.com/lit/an/snoa592c/snoa592c.pdf)

This design uses the ADD3501, and while I can't find anyone who is selling one, I was able to find a [rather old datasheet](http://www.datasheetcatalog.com/datasheets_pdf/A/D/D/3/ADD3501.shtml). This isn't too surprising, since the refernce design looks like it was originally published in 1980.

Internally, it looks like the ADD3501 has an ADC with a differential input and some kind of input-swapping arrangement that allows it to detect the input polarity in addtition to the absolute voltage. The max differential voltage on the ADD3501 input is 2V, and all of the ranging, scaling and other logic is done by dedicated circuitry.

Input protection is provided by 4 clamping diodes to clamp the ADC's differential inputs to VCC and ground. The input divider circuit provides the resistance necessary to avoid high currents through these clamping diodes in an overvoltage event.

Current measurements are taken by measuring the voltage across a shunt resistor, ranging from 1 Ohm (2A range) to 10k Ohms (200uA range). Given the 2V input range of the ADD3501, this makes sense, but the 2V drop seems like a lot. I'm not sure I want to use this part of the reference design.

Ranging is performed by a vast array of mode selector switches that route the input leads through a variety of 1% and 0.1% resistors.

The ADD3501 datasheet describes how an input voltage range below 2V can be measured by placing a divider on the ADC feedback path, but the refrence design does not appear to do this, and its lowest measurement range is 2V.

AC measurement is accomplished by a sequence of op-amps that invert the negative half of the AC waveform and then average it, with an output divider that results in the RMS value of the input. The application note mentions an LH0091 true-rms-to-DC convertor that can replace this circuit.

Resistance measurement is accomplished by a constant-current source, constructed from a constant-voltage source and an adjustable source resistor driving an unknown load resistance. The drive current for each range is selected so that the output voltage has a direct relationship to the resistance. The maximum voltage of the constant current source is selected so that it is higher than the maximum input voltage of the ADC, so that with increasing load resistance, the output voltage passes 2V before the current drops.

## [ICL7103A/ICL8052A Auto-Ranging Reference Design](https://www.intersil.com/content/dam/Intersil/documents/an02/an028.pdf)

This application note describes two different resistor ladders for selecting the input range. It looks like this design and the previous design both rely on resistor ladders similar to Type A. This design cleverly re-uses the same resistor ladder for both voltage and resistance measurements.

This design uses a similar but simpler, single op-amp circuit for converting AC measurements to DC.

The current source seems to be different, but operates in a similar way, by driving a constant voltage across a selectable resistance, to generate a selectable current, and then applying that current to the resistance under load to generate a voltage.

The ICL8052A contains the reference voltage, comparator and integrator stages of the ADC. The ICL7103A contains the analog switching stage and the integration logic to count the relationship between the reference and input voltages. This looks similar to the ADC stage on the ADD3501.

Instead of swapping the inputs to determine the voltage polarity, as in the ADD3501, this circuit uses dual-supply op-amps and detects the input polarity during the integration phase of the ADC. This means that the negative input lead to the DMM remains connected to ground at all times, unlike the ADD3501 design where both leads are floating with respect to the device ground in  voltage and current modes.

This design appears to achieve auto-ranging by detecting when the input is outside of the ADC range, either above 2V or below 10% of the range, and then automatically switching to a different range. It achieves this by using the over-range and under-range outputs on the ICL7103A to drive dedicated digital that increments or decrements a shift register that controls the range relays.

Since the input protection prevents unsafe inputs to the ADC on all voltage ranges, the auto-ranging just needs to detect that the ADC input is above the range that can be displayed to trigger a transition to a higher range.

The timing and design of the auto-ranging circuit is clever, but it uses a lot of parts and could probably be achieved more easily in a modern design using a microcontroller and a modest amount of code. Doing this in software would also allow more advanced functionality such as combined auto-ranging and manual range selection.

This design uses reed relays for range selection instead of selector switches, and only has one selection ladder instead of the three selection ladders on the previous design. The same resistor ladder implements range selection for voltage and resitance modes, and the current mode of the previous design isn't implemented here.