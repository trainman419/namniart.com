---
layout: post
title: How to Launch a URL from Terminal
excerpt: The open command really does a nice job of launching applications, files, urls, and more.
---

A quick discovery and a quick question:

The discovery first? It is simply this. Try it yourself, on your own command line:

{% highlight bash %}
$ open http://fictional.com 
{% endhighlight %}

And if you would like to have the url open in the background, just specify that with the -g option.

{% highlight bash %} 
$ open -g http://fictional.com 
{% endhighlight %}

Want to open it with a specific browser? Use the -a option to select an application:

{% highlight bash %} 
$ open -g http://fictional.com -a Safari.app
{% endhighlight %}


I'll leave it up to you to find out more (yes, there is more): man open

Now for the question:

What is your choice for launching applications, urls, files?

Also, I would just like to mention that the commands above may be specific to Mac OS X.
