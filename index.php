---
layout: default
---

<ul class="posts">
  {% for post in site.posts %}
    <li>
        <h2 class="title"><a href="{{ post.url }}">{{ post.date | date: site.datefmt }} - {{ post.title }}</a></h2>
          <p>
            {{ post.excerpt }}
          </p>
      </li>
  {% endfor %}
</ul>
