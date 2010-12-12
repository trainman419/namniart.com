<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta content="text/html; charset=utf-8" http-equiv="content-type" />
  <title>namniart - {{page.title}}</title>
  <link rel="stylesheet" href="/css/syntax.css" type="text/css" />
  <!-- Homepage CSS -->
  <link rel="stylesheet" href="/css/style-nik.css" type="text/css" media="screen, projection" />
</head>

<body id="post">
  <div id='content-wrapper'>
    <h1 id='page-header'>
      <a href="/">
        namniart
      </a>
    </h1>
    <div id='header'>
      <a href="/">Home</a> | 
      <a href="/about.html">About</a> |
      <a href="/projects.html">Projects</a>
    </div>

    <div id='content'>
      <h2>{{ page.date | date: site.datefmt }} - {{ page.title }}</h2>
      {{ content }}
      <a class="home" href="/">Home</a>
    </div>

    <div id= 'footer'>
      <a href="http://github.com/trainman419">Github</a>
    </div>
    </div>
  </body>
</html>
