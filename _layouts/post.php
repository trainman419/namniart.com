{% capture extratitle %} - {{page.title}}{% endcapture %}
{% include header.php %}
<div id='content'>
  <h2>{{ page.date | date: site.datefmt }} - {{ page.title }}</h2>
  {{ content }}
  <a class="home" href="/">Home</a>
</div>
{% include footer.php %}
