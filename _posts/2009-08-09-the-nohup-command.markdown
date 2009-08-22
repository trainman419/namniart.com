---
layout: post
title: "How to Start a Background Job"
excerpt: The nohup command is handy when you need to run a command even after you log out of your terminal session.
---
 
The `nohup` command is handy when you need to run a command even after you log out of your terminal session.

For an example, I will say we have an important task that needs to run 
{% highlight bash %}
# This will boot ruby, print a message, and then sleep for 60 seconds
$ nohup ruby -e "puts 'Doing Important Work...'; sleep(60)"
{% endhighlight %}

After the command is issued, `nohup` redirects the command output to a `nohup.out` file in the home directory. During the minute that the command runs, I close my terminal window and open a new window. I run the following command:
 
{% highlight bash %}
$ ps ax | grep "Doing Important Work"

  32961 s001  S      0:00.01 ruby -e puts 'Doing Important Work...'; sleep(60)
{% endhighlight %} 

From the above, I see that the command is still running even when I log out of the session. Pretty cool!

Slightly related is the note that if you just want to run a program and continue using the same terminal (and realizing that if you logout, the process will be killed) then use an ampersand(&):

{% highlight bash %}
$ ruby -e "puts 'Doing Important Work...'; sleep(60)" &
{% endhighlight %}

The ampersand will make the command run as a background job. Finally, if you want to run the command in the background and be able to logout without killing the process, issue this:

{% highlight bash %}
$ nohup ruby -e "puts 'Doing Important Work...'; sleep(60)" &
{% endhighlight %}

For alternatives to background jobs, see: Basically Tech's  [Shell stuff, job control, and screen.](http://www.basicallytech.com/blog/index.php?/archives/70-Shell-stuff-job-control-and-screen.html)