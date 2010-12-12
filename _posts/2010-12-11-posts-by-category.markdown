---
layout: post
title: "Posts by category"
excerpt: "I managed to get Jekyll to display posts by category"
categories:
- jekyll
---

I managed to convince jekyll to display posts by category. I hacked at it for a while on my own, and almost kinda got somewhere, but I kept running into the issue that I don't really understand how <a href="https://github.com/tobi/liquid">Liquid</a> interprets the data structures that represent categories of posts passed in by Jekyll. I took to google and found a <a href="http://groups.google.com/group/jekyll-rb/browse_thread/thread/5bff27f3fcafa29c">thread</a> that does mostly what I was looking for, but it seemed to have a bug, so I modified it and ended up with this:

<a href="https://github.com/trainman419/namniart.com/blob/91bc271d81fdec031cd944e22c0ba8ef15783137/categories.html">Initial version on github</a>

And it works!

P.S.
Linked to github because Liquid doesn't have an escape sequence.
