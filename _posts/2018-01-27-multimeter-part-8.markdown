---
layout: post
title: "Nixie MultiMeter, Part 8"
excerpt: Schematic Streaming and Voltage Input Protection
categories:
- Electronics
- PCB
---

## Streaming

As I move from doing circuit design math to creating the schematic, I'm going to be trying to livestream the circuit design work when I can.

## Livestream 1: Getting Started

<iframe width="560" height="315" src="https://www.youtube.com/embed/DONPidhnXd4?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen="allowfullscreen">
</iframe>

## Livestream 2: More work on the voltage divider ladder

<iframe width="560" height="315" src="https://www.youtube.com/embed/yACl8GQqf80?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen="allowfullscreen">
</iframe>

## Input circuit protection

Since I completed the second livestream, I've done a bit more work designing the input protection circuit for the voltage divider ladder. Taking some good advice from [The Art of Electronics](https://www.amazon.com/Art-Electronics-Paul-Horowitz/dp/0521809266/), I'm using low-leakage JFETs as diodes to clamp the voltage at the op-amp input. I had a very hard time finding data about operating JFETs with a forward bias like this. Most documentation I've found says that the gate is a PN juntion and will operate as a diode when forward biased, but that the current capability is limited and so it's not recommended. Very few sites and datasheets have documentation on the actual forward current limit, but it seems to be in the low tens of milliamps. Most of the models in The Art of Electronics and elsewhere seem to treat the forward-bias mode of the JFET gate more like a resistance in the 2-5k range and less like a diode, so I'll use that model to estimate the clamping voltage.

I've selected the MMBF4117 as my protection JFET, and my best guess on the forward-bias gate current is 10-50mA, with leakage in the low picoamp range when the voltage is around 10mV.

I've added a 1M resistor to the back side of the ladder to limit the current in the lowest range and prevent excess current flow if multiple relays in the ladder are closed at the same time. In addition, the input goes through a 100k resistor before the protection JFET. This means that, depending on which tap on the ladder is selected, the impedance between the input and the JFET will be between 1.1M (10mV range) and 11.2M (all other ranges). Since both of these resistors are exposed to minimal current, their absolute value and tolerance doesn't affect the circuit design, so I've selected commodity 5% tolerance parts for both of these (significantly less expensive than the 0.1% tolerance resistors in the voltage divider).

For these input ranges, I can calculate a leakage current that corresponds roughly to 1 count by dividing the voltage step in the lowest range by the measurement path impedance.

I can also estimate the JFET current and clamping voltage by modeling the JFET that is in forward bias as a low resistance, and since it is several orders of magnitude lower than the measurement path impedance, I can assume it is 0 to make the current calculation easier.

| Measurement Path Impedance | Maximum Leakage      | JFET current at 2kV input |
|----------------------------|----------------------|---------------------------|
| 1.1M                       | ~1 pA (1uV / 1.1M )  | 1.8mA                     |
| 11.2M                      | ~1 pA (10uV / 11.2M) | 0.18mA                    |

## Op-Amp Selection

Given the ~1pA leakage current requirement and 1uV resolution on the input, I need a low-leakage, low-offset precision op-amp for the input. Again, The Are of Electronics has a lot of great suggestions here, and after reviewing their table of precision op-amps I selected the LTC6078 since it looks like it has a good balance of maximum leakage (~1pA) and offset voltage (~25uV). This op-amp doesn't have particularly high bandwidth or slew rate, but it's only driving the high-impedance load of the MSP430's ADC, and the expected sampling rate is low, so I'm not too worried about the low bandwidth.

Similar to the TI reference design, I'm using these op-amps as unity gain buffers on the voltage and current stages, and I'm also using one to generate the mid-supply bias voltage for the common probe input.

## Current Measurement Resistors

With the voltage input side mostly complete, it's time to start designing the current input.

Looking at the current input ranges that I've chosen, I think I want to switch from 3 current ranges to 4, to get better resolution across the entire range of the meter. I'm going to keep the 10uA and 10A ranges, and choose two new ranges at 100x intervals, so that the new ranges will be:

 * 10uA
 * 1mA
 * 100mA
 * 10A

This means that if I'm measuring currents in the 100uA or 10mA range, I'll still have about 3 digits of resolution, without the added complexity of having ranges for every 10x increment.

Since 1.4mOhm resistors are hard to find, I've chosen current sense resistors that are multiples of 1.5 instead.

| Range | Resistor Value | DigiKey Part |
|-------|----------------|--------------|
| 10uA  | 1.5k Ohm       | [P19990CT-ND](https://www.digikey.com/product-detail/en/panasonic-electronic-components/ERJ-PB3B1501V/P19990CT-ND/6214245) |
| 1mA   | 15 Ohm         | [YAG4833CT-ND](https://www.digikey.com/product-detail/en/yageo/RT0805BRD0715RL/YAG4833CT-ND/6616989) |
| 100mA | 150m Ohm       | [CSR2010FTR150CT-ND](https://www.digikey.com/product-detail/en/stackpole-electronics-inc/CSR2010FTR150/CSR2010FTR150CT-ND/2027110) |
| 10A   | 1.5m Ohm       | [696-1184-1-ND](https://www.digikey.com/product-detail/en/riedon/CSR2512C0R0015F/696-1184-1-ND/2813310) |
