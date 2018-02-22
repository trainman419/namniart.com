---
layout: post
title: "Nixie MultiMeter, Part 9"
excerpt: Current Sense Input Protection
categories:
- Electronics
- PCB
---

## Current Sense Protection

For protection on the current side, at a minimum I want to protect against the current measurement leads of the meter accidentally connected across mains (120V RMS AC, 180V peak).

The two common overcurrent protection methods seem to be fuses (particularly the HRC variety) and PTCs (Positive Temperature Coefficient resistors).

If I use a PTC, I'll need a clamping diode across the current shunt, to limit the current through the shunt resistor until the PTC activates. A PTC is a Positive Temperature Coefficient resistor. At currents below its rated trip threshold, it has a low resistance, but if a high current flows, it causes the PTC to heat up and significantly increase its resistance, which limits the current. By adding a diode in parallel with the current sense resistor, I can clamp the voltage across the current shunt resistor and provide a safe path for the overload current until the PTC reaches its high-resistance state.

When the PTC is active, it will be subjected to the majority of the voltage (minus the diode drop), so it should have a high voltage rating. Since my maximum range is 10A, the PTC should have a carrying current of at least 10A. When the protection diode is active, before the PTC has reached its high-resistance state, the diode will carry the overload current (possibly for many seconds) so it will need to have a high current capacity.

If I use a fuse, I'll probably still need a clamping diode across the current shunt to limit the shunt resistor current. Similar to the PTC, the diode will need to carry the overload current until the fuse blows, but a fuse can be selected with a shorter blow time, so the diode will be exposed to the overload current for less time, and therefore I could choose a smaller diode.

In both cases I might also want to add a low-value resistor in series with this diode to absorb some of the power and reduce the current, in the case where the meter is subjected to a high-voltage, high-current supply (such as mains voltage). I'm not quite sure which of these circuits will fit my requirements best, so I'm going to evaluate four circuit configurations:

 1. PTC and diode
 2. PTC, resistor and diode
 3. Fuse and diode
 4. Fuse, PTC and diode

I'd like my current input to survive contact with AC mains voltage, so I'll evaluate all four against a 200V supply voltage (slightly worse than expected peak voltage). In particular, I'm looking to evaluate the final voltage applied to the current shunt resistor, the current through the shunt resistor, and for the PTC, the voltage across the PTC. I'll also need the power dissipated in each component as a rough metric for component cost, and for final component selection if I choose that configuration.

A quick analysis of the first circuit configuration suggests that I need to about this differently. With a clamping diode that clamps to 0.7V, that 0.7V will be present across the 1.5m Ohm sense resistor, and it will carry about 467A. I need to decide if this is acceptable or if I need to find a different surge supression method.

Here's how a few reference designs handle this:
 * TI's ADD3501 reference design has a 2A range with a minimum 1 Ohm sense resistor, and no overcurrent protection.
 * TI's TIDA-00879 (MSP430) design has a 60mA range with a minimum 0.5 Ohm sense resistor and a 200mA PTC to reduce current in an overvoltage condition. As noted in my pervious post ([Part 5](2018-01-18-multimeter-part5.html)), the PTC is poorly positioned such that it could subject the op-amp input to excess voltage in an overcurrent situation.
 * The [Fluke 27](http://assets.fluke.com/manuals/25_27___smeng0000.pdf) has a 10A range with a 5m Ohm sense resistor, and uses a simple 15A fuse for overcurrent protection. They also have L1 on the common side, which probably helps reduce dI/dT and therefore the peak current in overvoltage scenarios.
 * The Fluke 27 also uses a diode shunt around the low-current ranges, to prevent overcurrent through the low-current shunt resistors.

 Fluke's design seems to be the only one that is close to what I'm designing and which has circuit protection, and it suggests two different protection methods depending on the current range: fuses for higher-current ranges, and fuses (or maybe a PTC) combined with shunt diodes for lower current ranges. The effectiveness of the diode shunt and therefore the current ranges that it should be applied to are limited by the current through the sense resistor at the clamp voltage, so I'll compute that for my four sense resistances:

| Range | R(sense) | Current at 0.7V | Power at 0.7V | Current at 200V (no diode shunt) | Power at 200V (no diode shunt) |
|-------|----------|-----------------|---------------|----------------------------------|--------------------------------|
| 10A   | 1.5m Ohm | 467A            | 327W          | 133k A                           | 26.7 MW  (kaboom!)             |
| 100mA | 150m Ohm | 4.6A            | 3.3W          | 1.3k A                           | 267 kW   (pop!)                |
| 1mA   | 15 Ohm   | 46mA            | 32mW          | 13.3 A                           | 2.6 kW                         |
| 10uA  | 1.5k Ohm | 0.46mA          | 0.32mW        | 133 mA                           | 26.7 W                         |

 Clearly, trying to use a diode shunt around the 1.5m Ohm resisor won't prevent its destruction. The 150m Ohm resistor that I've chosen has a continuous power rating of 1W, but from the datasheet it looks like it will survive this kind of overcurrent for 5 seconds. The current and power in the 15 Ohm and 1.5k Ohm resistors is well below their limits. The current and power through all of these resistors at line voltage is clearly destructive, so it seems obvious that all of them need some form of overcurrent protection.

Browsing over PTCs on DigiKey, I don't see any PTCs with a combined 10A, 200V rating, so it looks like I'll need a traditional fuse there.

Given that the 15 Ohm and 1.5k Ohm resistors probably won't blow a 10A fuse at short circuit (fuses require some overcurrent before they open). At slightly lower voltages, the 15 Ohm resistor could carry 10A (and disspate 1500 watts!) without blowing the fuse, so the lower current ranges definitely need an alternate, lower-current protection. DigiKey has a number of PTCs with ratings above 200V and 1mA (often much above 1mA).

PTCs have specifications for holding and trip currents, and a trip current time that specifies how long it takes to open at the trip current. These specifications seem to have quite a spread of values across different parts, but they only seem to be available in limited combinations, so it seems like the most appropriate way to proceed here is to choose a PTC that is close to the correct specification and then design the rest of the circuit around that component.

With that in mind, the following components look good:

| Part | Hold Current (mA) | Trip Current (mA) | Nominal Resistance (Ohms) | Trip Time |
|------|-------------------|-------------------|---------------------------|-----------|
| [Bourns CMF-RL55A-0](https://www.digikey.com/product-detail/en/bourns-inc/CMF-RL55A-0/CMF-RL55A-0-ND/1679670)            | 50mA | 100mA | 55Ω | 100mS |
| [Bourns CMF-RL50A-0](https://www.digikey.com/product-detail/en/bourns-inc/CMF-RL50A-0/CMF-RL50A-0-ND/1679669)            | 50mA | 100mA | 50Ω | 100mS |
| [Bourns CMF-RL35-0](https://www.digikey.com/product-detail/en/bourns-inc/CMF-RL35-0/CMF-RL35-0-ND/1679668)               | 75mA | 150mA | 35Ω | 150mS |
| [Bel Fuse 0ZRE0005FF1C](https://www.digikey.com/product-detail/en/bel-fuse-inc/0ZRE0005FF1C/507-1348-ND/1560203)         | 50mA | 120mA | 18.5Ω | 15s |
| [Littelfuse LVR005NS](https://www.digikey.com/product-detail/en/littelfuse-inc/LVR005NS/LVR005NS-ND/1853845)             | 50mA | 120mA | 18.5Ω | 10s |
| [Bourns MF-RM005/240-2](https://www.digikey.com/product-detail/en/bourns-inc/MF-RM005-240-2/MF-RM005-240-2CT-ND/3594925) | 50mA | 120mA | 18.5Ω | 10s |
| [EPCOS B59907A0120A062](https://www.digikey.com/product-detail/en/epcos-tdk/B59907A0120A062/495-75886-1-ND/7086954)      | 12mA | 22mA | 640Ω | ~1s |

Given the 15 Ohm resistance of the 1mA sense resistor, the PTCs with higher resistance would cause a significant additional voltage drop (particularly the 55Ω and 640Ω components). Even though the PTCs with lower resistances have significantly higher current ratings, I think they're the correct choice for this circuit, and combined with a clamp diode they should still provide adequate protection for the sense resistor and the op-amp input stage.

Within the remaining components, it looks like there are three that meet similar specs (50mA current, 18.5Ω, 10 second) and two others with similar, higher resistances and faster trip times (50mA, 50Ω, 100mS). Looking closer, the three low-resistance parts all have maximum currents of 1A, but with a resistance of about 18.5 Ohms at 200V, the expected current is a little over 10A, which is well above the PTC's rating. The higher resistance devices all have maximum currents of 3A and a resitance of about 50 Ohms, which equates to a 4A current. In either case it looks like I'll have to take additional steps to limit the short-circuit current through the PTC if I want to use one of these.

I'm going to sleep on this and try to draw up some candidate circuit diagrams over the next few days.