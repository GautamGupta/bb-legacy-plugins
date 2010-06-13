==Description==
SourceCode HighLighter is a plugin developed for bbPress that highlight source code in a message. SourceCode HighLighter is developed usign Generic Syntax Highlighter (GeSHi).
SourceCode HighLighter URL: http://www.victorcuervo.com/mis-proyectos/sourcecode-highlighter-for-bbpress/

Tags:  code, formatting, syntax highlight

Tested over bbPress version 1.0

==Versions==
*SourceCode HighLighter v1.0
**First version

==Install==
1. Unzip the plugin archive.
2. Copy sourcecode-highlighter folder into you bbPress plugin directory.
3. Activate the plugin

==How use it?==
1. Use the PRE tag to highlight the code.
2. Optionally you can use the LANG attribute to indicate the programming language to use as a format.
3. Optionally you can use the LINENO attribute to indicate the line number for which will begin to number the code. See supported sourcecode languages by GesHi.
4. Use single quotes for LANG and LINENO attribute values.

==Sample==
Just copy&paste this code in a bbPress message:

<pre lang='java' lineno='1'>
public class HolaMundo {
  public static void main(String[] args) {
    System.out.println("Hola Mundo");
  }
}</pre>

Use single quotes for lang and lineno.

== Websites that use SourceCode HighLighter ==
* Dudas de Programacion (http://www.dudasprogramacion.com)

== Download ==
Download the lastest version http://code.google.com/p/vcp/