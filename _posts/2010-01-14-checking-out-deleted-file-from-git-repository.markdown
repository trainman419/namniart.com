---
layout: post
title: Checking out deleted file from git repository
excerpt: git checkout "commit" -- "filename"<br />git checkout 1A2B3C -- public/index.html
---

As I was using git in my development workflow, I learned that I could check out a file that was deleted in previous commit. I found this by scanning the 'git-revert' man page.

{% highlight bash %}

% man git-revert
...
Note: git revert is used to record a new commit to reverse the effect of an earlier commit
      (often a faulty one). If you want to throw away all uncommitted changes in your working
      directory, you should see git-reset(1), particularly the --hard option. If you want to
      extract specific files as they were in another commit, you should see git-checkout(1),
      specifically the git checkout <commit> -- <filename> syntax. Take care with these
      alternatives as both will discard uncommitted changes in your working directory.
...      
{% endhighlight %}


{% highlight bash %}

$ git checkout "commit" -- "filename"
$ git checkout 1A2B3C -- public/index.html

{% endhighlight %}