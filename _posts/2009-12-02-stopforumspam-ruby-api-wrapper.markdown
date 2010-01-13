---
layout: post
title: "StopForumSpam Ruby Api Wrapper"
excerpt: "Use my Stop Forum Spam api wrapper to detect and report spammers!"
---

## Why I need it
Recently, I realized that a forum I moderate was getting loads of spammers. 

"This, I cannot have", said I.

After searching the internet a bit, I found ["StopForumSpam.com"](http://www.stopforumspam.com/). SFS contains over 503213 records of spammer information. Lucky for me, SFS provides an [api to their data](http://www.stopforumspam.com/apis).

I started cleaning up the forum by writing a script that scanned each user and deleted the user if they were detected by SFS. 
Well, by the end of my spammer clean up, I decided it would be a fun challenge to write an api wrapper in ruby...

## How to get it
{% highlight bash %}
sudo gem install gemcutter
sudo gem tumble
sudo gem install httpary
sudo gem install stop_forum_spam
{% endhighlight %}


## How to use it

### Detecting a spammer
From inside an irb session, detecting a spammer goes like this:

{% highlight ruby %}
require 'rubygems'
require "stop_forum_spam"

# Detect a spammer by email
StopForumSpam::Spammer.is_spammer?('12@42up.com')
# Detect a spammer by username
StopForumSpam::Spammer.is_spammer?('broocorkidica')
# Detect a spammer by ip address
StopForumSpam::Spammer.is_spammer?('212.235.107.199')
{% endhighlight %}

Slightly more advanced usage looks like this:

{% highlight ruby %}
require 'rubygems'
require "stop_forum_spam"

# Instantiate a spammer object for extra details
spammer = StopForumSpam::Spammer.new('12@42up.com')

spammer.id #=> "12@42up.com"
spammer.type #=> "email"
spammer.last_seen #=> "2009-12-03 11:39:48"
spammer.frequency #=> "56"
{% endhighlight %}


### Reporting a spammer
Now, let's say that you discover someone spamming your site and you would like 
to report the spammer to the Stop Forum Spam database. 

You'll go through a process like this:

* [Sign up for a Stop Forum Spam API Key](http://stopforumspam.com/signup)
* Use the api key as an argument to the StopForumSpam::Client#new method
* Call the "post" method on the client with parameters ':ip_address, :email, and :username'

{% highlight ruby %}
require 'rubygems'
require 'stop_forum_spam'

# Instantiate a client object with the api key as a parameter
client = StopForumSpam::Client.new('123456789')

# Call ".post()" with spammer parameters
client.post(:ip_address => '127.0.0.1', 
            :email => 'spammer@ru.com', 
            :username => 'spammer')
{% endhighlight %}

## Alternatives

Of course, I'm not the first to develop an api wrapper for SFS. Here are a few others that might interest you:

- pwood's ruby wrapper called "sfs": [http://github.com/pwood/sfs](http://github.com/pwood/sfs)
- Others can be found by visiting: [http://www.stopforumspam.com/downloads/](http://www.stopforumspam.com/downloads/)