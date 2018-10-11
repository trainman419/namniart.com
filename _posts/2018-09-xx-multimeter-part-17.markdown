---
layout: post
title: "Nixie MultiMeter, Part 17"
excerpt: ""
categories:
- Electronics
- PCB
---

# Power

For this post, I will be focusing on as many of the parts of the power supply as I can fit into an evening.

## 3.3V Power Supply

The first three steps when choosing a power supply are to establish basic requirements for voltage, current, and noise. For the 3.3V power supply, the voltage was chosen since it is a common voltage, and is compatible with the MSP430 (although the MSP430F6736 can run from voltages between 3.6 and 1.8V). The current requirement will be driven primarily by the worst-case current for each component which is powered from 3.3V. For many of these componets, the worst-case current is a combination of the maximum output current and the device's internal worst-case current. The exact formulas can usually be found on the device's datasheet.

| 3.3V Load             | Worst-case Current              |
|-----------------------|---------------------------------|
| MSP430F6736           | 9.5mA, plus modules and outputs |
| LTC6078 (x2)          | 160uA each, plus outputs        |
| LMV358                | 340uA, plus outputs             |
| Resistor drive        | 100mA load on LMV358            |
| Virtual ground (200k) | 16.5uA                          |
| Reference Current     | 80uA                            |
| **Total**             | **110mA**                       |

Clearly, the largest component of the 3.3V load comes from the drive circuit for resistance measurement. That's also the part of the circuit where we're most likely to see overloads, so that power supply should probably have significantly more capacity. Sizing the power supply at 200mA (almost 2x the required current) seems like a safe bet, and it shouldn't be hard to find a switching (and maybe linear!) regulators that can do that.

Finally, the choice of power supply must consider noise, to help choose the type of regulator (switching or linear) and possibly choose several power supplies, if components need to have low-noise voltage supplies.

The MSP430F6736 datasheet strongly suggests powering the analog and digital supply pins from the same power supply. It seems like the obvious solution then is to use a single supply for all 3.3V loads, and add extra filtering on the supply for the high-precision part of the circuit. The amount of filtering required will be determined by the required output precision.

The ranges for both ADCs are +/- 14mV, over an 18-bit range, which comes out to about 107nV per bit. Any noise below that level will be undetectable by the ADC. The two components that will be affected by power supply noise will be the ADC within the MSP430, and the buffer op-amp.

### Background on Decibels

I see decibels on a regular basis, but I rarely have to do math with them, so I'll rehash the formulas here:

PSRR (dB) = 20 * log( power ripple / signal ripple )

Or, to go the other way:

power ripple / signal ripple = 10 ^ ( PSRR / 20 )

### ADC Power Noise

The ADC has a power supply rejection ratio of -79dB or 1/8913; this means that any noise on the power supply will apear on the ADC reading, reduced by a factor of about 8913. Working backwards, the power supply ripple would have to be above 950uV ( 107nV * 8193 ) to affect the ADC reading.

This is a bit bogus, because the ADC only specifies a PSRR at 50Hz, but the average switching power supply operates between 100kHz and 2MHz. PSRR tends to be heavily frequency-dependent, with more coupling from power supply to ADC at higher frequencies, so this value is at best a lower bound, and at worst completely useless.

It may be more useful to look at the integration time of the ADC to determine how much power supply noise will affect the ADC readings. If the power supply noise is highly periodic with one or two major frequency components, and the ADC integration time is longer than several cycles, it seems likely that the power supply noise will average out before it appears on the ADC.

The ADC parameters that I selected (all the way back in part 6) were borrowed from TI's reference design; they are a 2MHz modulator frequency and a 64x oversampling, for 19 bits of ADC resolution at 32k samples per second. Getting a switching power supply that runs several times faster than 32kHz should be easy. Given that the Sigma-Delta convertor works by sampling at high speed, in order to avoid aliasing the switching power supply frequency should not be close to any of the harmonics of the 2MHz sampling rate.

Aliasing occurs when two processes overlap at constant intervals; for example a 500Hz switching regulator frequency would be 1/4 of the 2MHz modulator frequency, and could cause 1 in every 4 ADC samples to land on a significant peak or trough of the power supply ripple, which would cause the power supply noise to be over-represented in the final ADC sample.

Typically, ADCs will have an analog low-pass filter at half the sample rate to filter out high-frequency noise; this filter would also help filter out power noise from other components, but it probably won't help with noise induced within the ADC. For an ADC sampling rate of 32k samples per second, the [Nyquist–Shannon sampling theorem](https://en.wikipedia.org/wiki/Nyquist%E2%80%93Shannon_sampling_theorem) states the the cutoff frequency should be set at half of the sampling rate; in this case that will be 16kHz.

### LTC6078 Power Noise

The LTC6078 has a power supply rejection ratio of 97 dB, but it drops to zero around 4MHz:

![LTC6078 PSRR vs Frequency](/media/2018/08/27/LTC6078_PSRR.png)

Since the ADC will have a low-pass filter on the input, we only need to worry about noise rejection in the LTC6078 up to the cutoff frequency of 16kHz.

At 16kHz and above, the PSRR is only about 25dB, or a reduction of about 17x between ripple on the power supply and ripple in the op-amp's output. This means that a power supply ripple as small as about 1.7uV would be output as ripple of 100nV, and could appear in the ADC output.

Reducing the cutoff frequency to 10kHz would result in a PSRR of about 40dB, which is a 100x reduction in power supply ripple, and puts the maximum power supply ripple at 10uV.

Power supply ripple for switching supplies is typically in the 250kHz to 1MHz range, so either of these filters will probably be acceptable.

## Power Filter

It looks like the analog portion of the MSP430 can draw as much as 10mA. Each 24-bit ADC can draw a maximum of 1mA, not counting the current required to run the voltage reference. The 10-bit ADC can draw a maximum of 185uA, including the current required to run the voltage reference. Combined with 160uA for the LTC6078, the total current for the precision analog side of the circuit is about 11mA.

TI has a nice whitepaper on [Filtering Techniques: Isolating Analog and Digital Power
Supplies in TI’s PLL-Based CDC Devices](http://www.ti.com/lit/an/scaa048/scaa048.pdf), which describes common power filtering techniques and recommends a ferrite bead and ceramic capacitor filter between the digital and analog supplies.