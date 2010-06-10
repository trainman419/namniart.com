---
layout: post
title: How to Launch a URL from Terminal
excerpt: Mac OS X and Linux provide nifty tools for launching applications, files, urls, and more from terminal.
---

Mac OS X provides the "open" command:

{% highlight bash %}
$ open http://lakedenman.com 
{% endhighlight %}

And if you would like to have the url open in the background, just specify that with the -g option.

{% highlight bash %} 
$ open -g http://lakedenman.com 
{% endhighlight %}

Want to open it with a specific browser? Use the -a option to select an application:

{% highlight bash %} 
$ open -g http://lakedenman.com -a Safari.app
{% endhighlight %}

Linux provides the following commands: "gnome-open", "exo-open", and the "xdg-open" commands. 

Use gnome-open when you're wanting a gnome preference, use exo-open when you want an xfce preference, and xdg-open when you're wanting you're preferences.

I prefer xdg since it's set to use my specified preferences.

{% highlight bash %} 
$ xdg-open http://lakedenman.com 
{% endhighlight %}

