---
layout: post
title: "Nixie MultiMeter, Part 4"
excerpt: Building a Nixie MultiMeter, Part 4
categories:
- Electronics
- PCB
---

Last few days have been busy, but I've been keeping multimeter ideas in the back of my head.

I'm leaning pretty strongly towards designing the multimeter around a standard ADC IC and microcontroller, instead of using dedicated multimeter ICs, so today I'm going to spend some time looking for ADCs.

Based on the study of existing ADC designs that I did last time, I think I'm looking for a few key features in an ADC:

 * Some way to handle reverse voltages, either differential inputs with some kind of switching, or split voltage rails.
 * At least 14 bits of resolution, but probably more if I need extra bits to detect out-of-range inputs.
 * The ability to adjust the ADC reference voltage would be nice, because it could allow me to measure smaller voltages.

 I'm going to start my search in DigiKey's [Data Acquisition - Analog to Digital Converters (ADC)](https://www.digikey.com/products/en/integrated-circuits-ics/data-acquisition-analog-to-digital-converters-adc/700) category.

[![Initial ADC Search](/media/2018/01/14/digikey_search_1_thumb.png)](/media/2018/01/14/digikey_search_1.png)

I've started with a few basic search options:

 * In Stock and Normally stocking, because I want to pick parts that are readily available, and I want to choose something that will still be in stock in a few weeks or months when I finish up enough of the design details to order parts.
 * RoHS Compliant (controlled levels of harmful substances) because I want to choose parts that won't further harm the environment (almost all parts are RoHS compliant, so this doesn't limit the selection much)
 * Part Status: Active. This describes the manufacturer's production status, and Active parts are ones that are still being manufactured. I don't expect to need anything cutting-edge, so I won't choose Preliminary here.
 * Number of Bits: 12,16 , and all options 16 and higher. All I want to do with this filter is filter out the parts that absolutely don't have enough resolution. I can come back later and filter this more.

That still leaves 1900 parts, so I'm going to scroll through the remaining categories to try to narrow down my search a bit more.

[![Narrowing By Reference](/media/2018/01/14/digikey_search_2_thumb.png)](/media/2018/01/14/digikey_search_2.png)

One of the options is Voltage Refence, and I'm pretty sure I want an ADC with an external reference, so I'm going to select all of the options that include External. (presumably the ADCs that list multiple options are configurable, but if they're not I can always narrow the search further).

Setting a quantity of 1 pushes all of the full reels and tubes, which often have a minimum purchase quantity of hundreds to thousands, and sorting by price yields a the [Nuvoton NAU7802 24-bit ADC](http://www.nuvoton.com/resource-files/NAU7802%20Data%20Sheet%20V1.7.pdf) and the [Maxim MAX11205](https://datasheets.maximintegrated.com/en/ds/MAX11205.pdf). Both of these are differntial ADCs with adjustable reference voltages. The MAX11205 has a differential voltage reference, but the positive reference must be above the negative reference. This is intriguing, but doesn't seem particularly useful for my application. The NAU7802 has an internal temperature sensor that could be useful for calibrating out the temperature drift in the ADC and other parts of the circuit. Both parts have single-ended supply voltages, and require that the positive and negative input signals be between GND and the supply voltage.

Searching a bit more and trying to find ADCs that use dual supply voltages finds the [Cirrus Logic CS5530](https://d3uzseaevmutz1.cloudfront.net/pubs/proDatasheet/CS5530_F3.pdf), which appears designed mostly for audio use and also has a differential refernece voltage.

It seems like finding a dual-supply ADC with a single-ended referencce voltage adjustment will be difficult, and as I think about it that's not much of a surprise, given that it would require an ADC that was designed to rectify the voltage before doing the conversion, and I suspect there isn't much use for that outside of ADCs explicitly designed for multimeters.

I suspect it's possible to design the analog stage in a way that scales or shifts the voltage, but tuning the zero offset seems like it would be a challenge, and it isn't discussed much in the refence designs I've already reviewed.

I did a bit more searching and found a more modern multimeter refence design based on the TI MSP430, the [TIDA-00879](http://www.ti.com/tool/TIDA-00879). This design has a number of key specifications that make it very interesting for me:
 * Based on a Microcontroller (the TI MSP430)
 * Automatic range selection
 * 4.5 digit, 60,000 count design, which is more accurate than my eventual target.
 * Single-ended supply voltage
 * 60mV to 60V and 600uA to 60mA current ranges
 * Range selection appears to be electronic, not relay-based
 * Full design files, schematics and software available
 * Microcontroller drives the display directly (or almost directly)

This is an incredibly interesting design because it's already based on a popular microcontroller, which means that the only modifications that I will have to make to the design are the display change, and any range adjustments that I want for my design. Ideally my changes would be:
 * Change to 10mV, 100mV, 1V, 10V, 100V and 1000V ranges
 * Change to 10uA, 10mA, and 10A ranges
 * Add resistance measurement (100, 1k, 10k, 100k, 1M, 10M)
 * Look into adding a 4-wire resistance mode (for measuring very small resistances)
 * Change the display to nixie tubes

Obviously the lower voltage and current ranges may be difficult, there may be design changes that are difficult to handle, but overall I'm confident that I can adapt this design to my needs.
