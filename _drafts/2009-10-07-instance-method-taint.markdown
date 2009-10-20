---
layout: post
title: "Instance Method: Taint"
---
 
Let's talk about `taint`. First, let's realize that `taint` is an instance method.

{% highlight ruby %}
obj = Object.new
obj.respond_to?(:taint)
{% endhighlight %}

Now, let's look up a formal definition:

        taint

        obj.taint -> obj
        Object#taint (public)

        Marks obj as tainted---if the $SAFE level is set appropriately, 
        many method calls which might alter the running programs environment 
        will refuse to accept tainted strings.
 
So, taint simply marks objects as being tainted. That's easy enough to grasp, but how does ruby behave to tainted objects? 
