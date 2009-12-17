---
layout: post
title: "StopForumSpam Ruby Api Wrapper"
excerpt: "I recently built an api wrapper for checking if an ip, email, or username is related to a spammer."
---

Recently, I realized that a forum I moderate was getting loads of spammers. 

"This, I cannot have", said I.

After searching the internet a bit, I found ["StopForumSpam.com"](http://www.stopforumspam.com/). SFS contains over 503213 records of spammer information. The spammer information important to me is the username, the email, and the ip address. Lucky for me, SFS provides an [api to their data](http://www.stopforumspam.com/apis).

I started cleaning up the forum by writing a script that scanned each user and deleted the user if they were detected by SFS. 
Well, by the end of my spammer clean up, I figured it would be a fun challenge to write an api wrapper in ruby...

So, I wrote the "StopForumSpam" gem. Feel free to install it; it's [hosted on gemcutter](http://gemcutter.org/gems/stop_forum_spam):

{% highlight bash %}
sudo gem install gemcutter
sudo gem tumble
sudo gem install httpary
sudo gem install stop_forum_spam
{% endhighlight %}

From inside an irb session, basic usage goes like this:

{% highlight ruby %}
require 'rubygems'
require "stop_forum_spam"
StopForumSpam::Spammer.is_spammer?('12@42up.com') # by email
StopForumSpam::Spammer.is_spammer?('broocorkidica') # by username
StopForumSpam::Spammer.is_spammer?('212.235.107.199') # by IP
{% endhighlight %}

Slightly more advanced usage looks like this:

{% highlight ruby %}
...
spammer = StopForumSpam::Spammer.new('12@42up.com')
spammer.type #=> "email"
spammer.last_seen #=> "2009-12-03 11:39:48"
spammer.frequency #=> "56"
spammer.id #=> "12@42up.com"
{% endhighlight %}

I plan on implementing posting a spammer in the near future. If you would like to help, you can [find the source code on github.com](http://github.com/ldenman/stop_forum_spam).

Of course, I'm not the first to develop an api wrapper for SFS. Here are a few others that might interest you:

- pwood's ruby wrapper called "sfs": http://github.com/pwood/sfs  
- Others can be found by visiting: http://www.stopforumspam.com/downloads/

By the way, this is my first gem so I'm very excited about it!