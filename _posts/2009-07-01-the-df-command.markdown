---
layout: post
title: How to Check Available Hard Drive Space
excerpt: It's easy to use, but sometimes easy to forget the command that shows the amount of disk space left on your computer. 
---

It's easy to use, but sometimes easy to forget the command that shows the amount of disk space left on your computer. 
You can try this on your own command line. If you are running linux or mac os x, you should have this command available to you.

{% highlight bash %} $ df {% endhighlight %}

You'll get something like this:

{% highlight text %}
Filesystem    512-blocks      Used Available Capacity  Mounted on
/dev/disk0s2   976101344 662217616 313371728    68%    /
{% endhighlight %}

Oh, you don’t know what all those number are? You want it in a human readable format?

{% highlight bash %} $ df -h {% endhighlight %}

Look at you go!

{% highlight text %}
Filesystem      Size   Used  Avail Capacity  Mounted on
/dev/disk0s2   465Gi  316Gi  149Gi    68%    /
{% endhighlight %}

Now, you might want to check your servers and make sure your disk space has not run out.
