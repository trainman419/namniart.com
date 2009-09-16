---
layout: post
title: "A Fix for Postfix"
excerpt: Have you had trouble sending mail from your server to an email address of the same domain? Me too...
---
 
As I rails developer, I have had some trouble in the past while sending mail from my server to an email address that has the same domain.

If you are suffering from this problem, try checking to make sure that your hostname is not listed in postfix's main.cf file.

{% highlight bash %}
$ cat /etc/postfix/main.cf
{% endhighlight %}

Will ouput something similar to:

{% highlight text %}
  ...

  myhostname = cache
  alias_maps = hash:/etc/aliases
  alias_database = hash:/etc/aliases
  myorigin = /etc/mailname
  mydestination =  myawesomesite.com, localhost.localdomain, localhost
  relayhost = 
  mynetworks = 127.0.0.0/8 [::ffff:127.0.0.0]/104 [::1]/128
  mailbox_size_limit = 0
  recipient_delimiter = +
  inet_interfaces = all
{% endhighlight %}

You see how 'myawesomesite.com' is listed in "mydestination"? Well, that's the problem. Since I'm only sending mail and not receiving it, I need to get rid of the domain in "mydestination". Otherwise postfix believes that the destination is local. The destination is going to a Google Apps address with the same domain name. I really hope this helps someone out. 

Further Reading: 

[Topic which pointed me in the right direction](http://forum.slicehost.com/comments.php?DiscussionID=1903)

[What is mydestination?](http://www.postfix.org/postconf.5.html#mydestination)