---
layout: post
title: "Set the date in linux"
excerpt: sudo date MMDDhhmmYYYY
---

I was recently trying to upload a file to Amazon's S3 server. Unfortunately, every time that I tried to upload the file from my server, I received the error below:


    RequestTimeTooSkewed: The difference between the request time and the current time is too large.

After a bit of googling about, I realized that I should probably check the date on the server, so:

{% highlight bash %}
$ date
Mon Dec  7 23:12:18 EST 2009
{% endhighlight %}

Well, although you can't really tell, the date was behind about five hours from the actual eastern time. The first way that I tried to update the server time was to run `rdate.`

    rdate - get the time via the network
    
Well, it seems that I kept getting timed out when trying to use rdate. So, I got dirty and used plain old `date` to update the time.

    This is the quick and dirty solution...

    To display the current date/time

    {%highlight bash%}
    $ date
    {%endhighlight%}
    To set the date/time

    {%highlight bash%}
    $ sudo date MMDDhhmmYYYY
    {%endhighlight%}
    
    Example:
    {%highlight bash%}
    $ sudo date 043017212008
    {%endhighlight%}
    (that is the time of my posting)

    MM - Two digit month number
    DD - Two digit date
    hh - Two digit 24 hour system hour
    mm - Two digit minute
    YYYY - Four digit year code
    
That date was updated successfully and then I was able to upload to Amazon S3. Good times...

Answer found here: http://www.forum.psoft.net/archive/index.php/t-13307.html