---
layout: post
title: "Nixie MultiMeter, Part 14"
excerpt: Level Shifters and Load Drivers
categories:
- Electronics
- PCB
---

## Choosing a Level Shifter and Relay Driver FET

Since I have 10 relays and 5 lines of digital logic to shift up to 12V, I'll need at least 15 N-channel FETs. Luckily, the FETs required in both of these wildly different circuits are actually similar enough in turn-on voltage and drive current that I may be able to use the same FET or FET array for both. Since I need 15 channels, it will probably be cheaper to find parts and do the schematic with a FET array, although the layout may be slightly more difficult.

I had a hard time figuring out where to find the right FET arrays on digikey. Instead of being in the semiconductors/FET Arrays category, they're mostly built as special purpose relay drivers with additional integrated diodes, and are therefore filed under [PMIC - Power Distribution Switches, Load Drivers](https://www.digikey.com/products/en/integrated-circuits-ics/pmic-power-distribution-switches-load-drivers/726). From the previous post I determined that I need a maximum of 100mA for any one relay. Most of these are designed for driving much larger loads, so nearly any of them will be able to drive that load. The requirement that is more likely to be a problem is the driving voltage. The microcontroller outputs are only 3.3V, so any FET that I choose will need to turn on strongly enough with a 3.3V gate voltage that it doesn't overheat.

A brief aside on MOSFETs (or FETs as they're commonly called), the voltage between the Gate and the Source of the FET determines how much it constricts the flow of electrons. At 0V most FETs are "off" or effectively infinite resistance (below their maximum voltage, of course), and above a threshold voltage (usually between 1 and 5 Volts) the FET has reached maximum conduction and behaves as a low resistance, usually a few ohms or smaller. Increasing the gate voltage above this threshold doesn't cause the FET's resistance to decrease any more. In between 0V and the threshold voltage, the FET will act like a much larger resistor, and if there is enough voltage or current through it, it can overheat even if otherwise operated within its limits.

There are many load drivers on DigiKey, and one of the first and least expensive 8x load drivers is the [TBD62083A](https://www.digikey.com/product-detail/en/toshiba-semiconductor-and-storage/TBD62083AFWGEL/TBD62083AFWGELCT-ND/5514124). Luckily the [TBD62083A datasheet](https://toshiba.semicon-storage.com/info/docget.jsp?did=29893&prodName=TBD62083AFG) lists the minimum turn-on voltage for the TBD62083A as 2.5V. Since this is below the 3.3V output from my microcontroller, this load driver will work quite nicely.