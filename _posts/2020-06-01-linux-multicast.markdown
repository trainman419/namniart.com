---
layout: post
title: "Pitfalls and Traps in Linux Multicast"
excerpt: "Common traps when using IPv4 Multicast on Linux, and how to avoid them"
categories:
- Linux
- Multicast
- Network
- IP
---

# Pitfalls and Traps in Linux Multicast

IPv4 multicast seems like a wonderful idea: you send one packet, your network
processes it and sends a copy of it to each host that needs it. Over the years,
wiser network engineers than myself have learned that multicast on a routed
network can be difficult to configure and may not be worthwhile, but
multicast has found adoption on local networks as an alternative to broadcast,
and can be efficient if the majority of network switches support IGMP snooping.
There are, however, many pitfalls on the path to using multicast on a local
network.

If you want to use multicast on a Linux host with multiple network interface,
in addition to the usual pitfalls and traps, there are several well-hidden
tar pits where you can get stuck for days or weeks.

## Background on IGMP and Multicast Group Membership

### IGMP and Membership

The IGMP protcol is used by hosts to inform the routers and switches on their local network that they wish to receive multicast traffic. End devices send IGMP membership advertisement, and routers and IGMP-aware switches listen for these IGMP membership advertisements and ensure that multicast traffic is forwarded to those end devices. The IGMP protocol is defined by [RFC-1112 (IGMP v1)](https://tools.ietf.org/html/rfc1112), [RFC-2236 (IGMP v2)](https://tools.ietf.org/html/rfc2236) and [RFC-3376 (IGMP v3)](https://tools.ietf.org/html/rfc3376).

### IPv4 Multicast to Ethernet Layer 2 address mapping

To allow switches to handle IPv4 multicast traffic with minmal knowledge of IPv4, each IPv4 multicast address is mapped to an Ethernet MAC address by [Section 6.4 of RFC-1112](https://tools.ietf.org/html/rfc1112#section-6.4). The lower 23 bits of the IPv4 multicast address are placed in the lower 23 bits of the `01:00:5E:00:00:00` MAC address to create the Ethernet multicast address. (This results in multiple IPv4 multicast addresses mapping to the same Ethernet address).

### Multicast vs IGMP
When working with multicast, it is important to recognize that IPv4 multicast, Ethernet multicast, and IGMP are related but separate protocols. IGMP is used to dynamically define and manage multicast groups on a network, but the actual packet forwarding is handled by the network switches based on the packet's Ethernet multicast address, and by routers based on the packet's IPv4 multicast address.

In many switches, you may only be able to inspect IPv4 or Ethernet multicast address, and it is important to know how they are related in order to debug forwarding issues.

Some switches may also be configured with static multicast group memberships based on the IPv4 or Ethernet address of the group. The implementation of this is left entirely to the network switch vendor and varies significantly between switches.

### IPv4 Multicast Address Allocation
IPv4 multicast addresses are [allocated by the IANA](https://www.iana.org/assignments/multicast-addresses/multicast-addresses.xhtml). Generally, these addresses are allocated to protocols that operate on local networks and which need unique identifiers.

Of special note is the 239.0.0.0/24 range, which is allocated for use within an organization and which should not be routed across the internet. This range is defined by [RFC-2365](https://tools.ietf.org/html/rfc2365).
multicast address allocations.

## IGMP snooping switches without an IGMP Querier

IGMP snooping is a feature that can be enabled on many higher-end switches.
The switch listens for IGMP group membership advertisements, and for each
incoming group membership, it adds that port to the advertised multicast groups,
so that any multicast packets to those groups will be sent to that port. This
prevents multicast traffic from being delivered to ports that aren't interested in it. This saves bandwidth, and in some cases can prevent small devices from
being overwhelmed by large amounts of multicast traffic.

Switches with IGMP snooping also implement a group membership timeout: if the
IGMP membership report hasn't be received recently, then the group memberships
are removed from that port. This is necessary for proper handling of devices
that are attached through multiple switches, but there's a trap here: devices
only send multicast membership reports when they join a group, or when they
receive an IGMP membership query.

The network device that sends IGMP membership queries is a IGMP Querier. All of
the devices that I've seen so far either don't include an IGMP Querier, or have
it turned off by default. This results in the very fun "multicast works for a
few minutes and then stops" issue. Of course, this happens because the
multicast group memberships time out.

Of course, now that you know all of this, the fix is simple: set up an IGMP Querier! All of the IGMP-aware switches that I've seen include one, and turning it
on is usually simple. The only configuration parameter for it is a querier
address, which leads to the next issue...

## Choosing a poor IGMP Querier Address

Some posts on the network tell you that you can choose any address for your
IGMP querier, and some even suggest choosing 1.0.0.0, 2.0.0.0, etc so that it
is easy to control the priority of IGMP querier election. They are WRONG.

On multi-homed Linux machines (and probably other operating systems too), the
OS will drop any packets which originate from an implausible IP address. If
your Linux machine has multiple interfaces, and receives a packet from, say,
1.0.0.0 on an interface which only has local connectivity, Linux correctly
deduces that this packet could not have possibly come from that network, and
drops it. Since the IGMP querier is sending packets from the querier address,
this will cause Linux to drop the IGMP queries instead of responding to them.

TODO: diagram.

Again, the fixes here are pretty simple: allocate an IP address to your switch,
and set the IGMP to the switch's IP address, or allocate a dedicated IP address
on the same subnet that is explicitly for use by the IGMP querier.

## Sniffing Multicast traffic

Since switches with IGMP snooping only send multicast data to the ports that are joined to those groups, common packet capture tools such as tcpdump and wireshark will not capture any multicast traffic if they device where they are run is not joined to the multicast groups of interest.

The best solution that I have found for this is to either write custom python tols to join the relevant multicast groups and capture the traffic, or to configure the switch to forward all multicast traffic to the port in question; most switches call this "router mode."
isn't sending you any packets!

## Inbound Multicast on Multi-homed Hosts

When joining a multicast group with setsockopt IP\_ADD\_MEMBERSHIP, Linux allows
you to specify a local IP address to bind to, or if you specify INADDR\_ANY, it
will choose an address for you. If you choose INADDR\_ANY, linux chooses "an
appropriate interface"! On a computer with a single network interface this is
fine, but if you have more than one interface, there's a chance that INADDR\_ANY
will choose the wrong interface.

The fix here is a bit painful: you have to write your software to detect all of
the configured IP addresses and either ask the user to pick one, or join the
multicast group on all of them. Luckily, you can join multiple multicast groups
on the same socket, so this isn't too hard to manage.

TODO: python example

Sadly, outbound multicast behaves differently.

## Outbound Multicast on Multi-homed Hosts

Linux chooses the outbound interface for a multicast packet, not based on the
the interface that has the multicast subscription(s), but instead based on the
system's default route. This means that even if you've joined a multicast group
and explicitly specified an interface, Linux will ignore that interface and
send your multicast packets out the default route.

It also doesn't matter if you have multiple memberships on the same socket,
Linux still only sends one packet.

Linux provides setsockopt IP\_MULTICAST\_IF to specify the IP address of the
outbound interface for a socket, but this option can only have one option on a
socket, so if you want to send multicast packets to many interfaces, you have
to open a separate socket for each interface!

TODO: python example
