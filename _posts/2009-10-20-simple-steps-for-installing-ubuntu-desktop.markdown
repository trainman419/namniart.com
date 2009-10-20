---
layout: post
title: "Simple Steps for Installing Ubuntu Desktop"
excerpt: Thanks to branchus for spilling the beans about installing Ubuntu Desktop
---
 
Here are some simple steps I found to install Ubuntu Desktop.


  Just to make sure run sudo apt-get update Then run
  
{% highlight bash %}
$ sudo apt-get install ubuntu-desktop
$ sudo apt-get install gdm
$ sudo /etc/init.d/gdm start
$ sudo dpkg-reconfigure xserver-xorg
{% endhighlight %}
  
  Gdm is what handles your x system starting automatically instead of entering start x at the command line each time you boot. The reconfigure runs the xserver setup so you can configure your system monitor, video card etc.


Thanks branchus!


[http://ubuntuforums.org/archive/index.php/t-186298.html](http://ubuntuforums.org/archive/index.php/t-186298.html)
 
