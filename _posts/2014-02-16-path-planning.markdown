---
layout: pose
title: "Path Planning for Dagny"
excerpt: Path planning for cars is hard
categories:
- robot
---

I've spent the last few months experimenting with path planning for Dagny.

## Background

Dagny is an car-type robot, which means that I have a very constrained set of possible motions. In particular, I can move forwards and backwards, and can turn while moving, but can't turn in place or move sideways. If you've ever driven a car and tried to parallel park, you can understand how difficult it can be to maneuver a car-type vehicle.

![Parking](TODO "image of parallel parking")

These motion constraints mean that I have to make sure that the motion planners understand the motion constraints and can generate a path that doesn't have turn-in-place behaviors, and honors the minimum turning radius of the robot.

The overall plan to achieve this is to use the ROS navigation stack with the sbpl\_lattice\_planner, custom motion primitives and a custom local planner.

## SBPL

SBPL is the Search-Based Planning Lab, and the publish a set of planning libraries called lattice planners that discretize the state space, and define motions that connect one point and angle on the grid to another grid point. These motions are usually either a straight line, or a combination of a straight line and an arc, to achieve both the desired linear and angular offset between poses. Once the motion primitives are defined, they form a graph that can be searched with an algorithm such as A\* to produce the desired path.

Since the motion primitives define the search space and how the robot moves, having a good set of motion primitives is critical to producing good plans.

## Generating Motion Primitives

My strategy for generating motion primitives revolves around building primitives from combinations of three types of actions:
 * Linear segments with zero angular velocity.
 * Arc segments with constant angular velocity
 * Spiral segments with varying angular velocity and fixed angular acceleration.

Since Dagny's dynamics allow me to execute any turn at any speed, I can ignore linear velocity and acceleration in my models. Therefore, for the sake of simplicity, I generate all of my motion primitives assuming a constant linear velocity of 1m/s.

A linear segment is defined simply by:

{% latex %}
\begin{bmatrix}
x \\
y \\
\theta
\end{bmatrix}
=
\begin{bmatrix}
t * cos(\theta) + x_{0} \\
t * sin(\theta) + x_{0} \\
\theta_{0}
\end{bmatrix}

{% endlatex %}



