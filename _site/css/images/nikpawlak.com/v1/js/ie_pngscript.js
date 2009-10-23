 //<![CDATA[

 // If you don't want to put unstandard properties in your stylesheet, here's yet
 // another means of activating the script. This assumes that you have at least one
 // stylesheet included already. Remove the /* and */ lines to activate.


 if (document.all && document.styleSheets && document.styleSheets[0] &&
  document.styleSheets[0].addRule)
 {
  // Feel free to add rules for specific tags only, you just have to call it several times.
  document.styleSheets[0].addRule('*', 'behavior: url(iepngfix.htc)');
 }


 //]]>
