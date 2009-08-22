---
layout: post
title: "Monitoring the System with Monit"
excerpt: Monit is a utility for managing and monitoring processes, files, directories, and filesystems on a Unix System. 
---
 
I'm on OS X and I just downloaded "Monit" via Macports:

{% highlight bash %}
$ sudo port install monit
{% endhighlight %}

I need to be able to monitor some things on my box. Namely, File System Size, CPU, MySQL and Nginx. Fortunately, Monit can easily take care of monitoring many resources and then reporting feedback.

## Setting up Alerts

Figuring out who needs to be informed when something is acting up is important. Monit will log any errors to a specified log file. Also, Monit allows emailing alerts. In order to enable file logging and email logging, you have to set the `logfile` and `alert` keywords in the `/etc/monitrc` file.

{% highlight text %}
set alert lake@lakedenman.com
set logfile /var/log/monit.log
{% endhighlight %}

Now, when any of the monitored services act up, Monit will let the alertee know right away! I'm itching to start add checks on system services, processes, and devices, so let's go.

## Setting System Checks


### File System Space
The first resource I want Monit to monitor is [disk space](http://mmonit.com/monit/documentation/monit.html#space_testing). 

I added this snippet to my `/etc/monitrc` file:

{% highlight text %}
check device lake with path /
  if space usage > 90 % then alert
{% endhighlight %}

The line above simply checks my root filesystem and alerts me if the space grows above 90 percent. Now that the disk usage is being monitored, I want to add a check for CPU usage.

### CPU Usage
The snippet below will tell Monit to monitor the cpu usage of the system:

{% highlight text %}
check system lake.local
  if cpu usage (user) > 70% then alert
  if cpu usage (system) > 30% then alert
  if cpu usage (wait) > 20% then alert
{% endhighlight %}

Okay, so Monit will alert me when the CPU Usage is too high. It's time to tell Monit to take a look at MySQL. 

### MySQL

{% highlight text %}
check process mysql
  with pidfile "/usr/local/mysql/data/lake.local.pid"
  start program = "/Library/StartupItems/MySQLCOM/MySQLCOM start"
  stop program = "/Library/StartupItems/MySQLCOM/MySQLCOM stop"
  if failed host 127.0.0.1 port 3306 then restart
  if 5 restarts within 5 cycles then timeout
{% endhighlight %}


[Monit Manual](http://mmonit.com/monit/documentation/monit.html)
