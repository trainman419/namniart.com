---
layout: post
title: Change navigation images with CSS
excerpt: We don't need any JS or server side junk!

---

This is one's for me. For my memory. For the times when I find myself wondering: 

    How do I programatically add an 'active' class
    on these navigation elements, depending the page I am on?
    
<small>the answer may not be all that obvious...</small>

You may find yourself wanting to do something like this with PHP:

{% highlight php %}
<html>
  <head>
    <title>
      Our Social Network
    </title>
  </head>
  <body>
    <div id='nav'>
      <a href="/" class="<? print url($_GET['q']) == '/' ? 'active' : '' ?>" title="">
        HOME
      </a>
    </div>
  </body>
</html>
{% endhighlight %}

That messy view logic really shouldn't be there at all. No, not under any condition!

So, let's make it work without PHP, without Javascript, and without any other server side script.

Okay, we'll go ahead and add an id of "home" to the body tag. And then we need to add an id of "nav-home" to the link tag.

Now that our html hooks are set up, we need some styyyyyyle maaaan. 

For our example, we'll just messy up our beautiful html by plopping in a some crummy css like shown below. 


{% highlight html %}
{% demo example-1.html %}
<html>
  <head>
    <title>
      Home: Our Social Network
    </title>
    <style type="text/css" media="screen">
      #home #nav-home { color: red; }
    </style>
  </head>
  <body id="home">
    <div id='nav'>
      <a href="/" title='Home' id='nav-home'>
        HOME
      </a>
    </div>
  </body>
</html>
{% enddemo %}
{% endhighlight %}

At this point, the output looks like this:

{% demolink example-1.html %}

An [example](/demos/2010-01-27-change-navigation-images-with-css/example_1.html "Title")

