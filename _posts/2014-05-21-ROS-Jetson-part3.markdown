---
layout: post
title: "ROS on the Jetson TK1 - part 3"
excerpt: Building ROS on the NVIDIA Jetson TK1
categories:
- Jetson-TK1
- Ubuntu
---

Things hung up overnight on the builds. Again.

I bumped into the assimp bug after building for 78 minutes:

```
Linking CXX executable /home/ubuntu/ros_catkin_ws/devel_isolated/collada_urdf/lib/collada_urdf/urdf_to_collada
/home/ubuntu/ros_catkin_ws/devel_isolated/collada_urdf/lib/libcollada_urdf.so: undefined reference to `vtable for Assimp::IOSystem'
/home/ubuntu/ros_catkin_ws/devel_isolated/collada_urdf/lib/libcollada_urdf.so: undefined reference to `typeinfo for Assimp::IOSystem'
collect2: error: ld returned 1 exit status
```

I ended up removing the following packages that depended on assimp:

 * robot_model/collada_urdf
 * rviz, and a few things that depended on it:
  * visualization_tutorials
  * rqt_robot_plugins/rqt_rviz

After that, the remainder of ROS built in about an hour.

*SUCCESS!*

The next steps will be to find some interesting projects to do with the new ROS and OpenCV libraries, try to build debs for PCL and possibly a patched version of assimp, and then move on to doing ROS debs.
