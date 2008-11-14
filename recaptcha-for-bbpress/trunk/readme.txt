=== reCAPTCHA for bbPress  ===
Tags: recaptcha, captcha, bots, spam, users, register, registration
Contributors: Dmitry Chestnykh
Requires at least: 1.0-alpha1
Tested up to: 1.0-alpha2
Stable tag: 1.0-alpha2

Add reCAPTCHA human test to registration page.

== Description ==

This plugin adds reCAPTCHA challenge to bbPress registration page to
protect it from automated bot registrations.

reCAPTCHA is a freely available CAPTCHA implementation by Carnegie Mellon University that not only helps protect websites against spam, but also
helps digitize books.

Note: You need to get API keys from http://recaptcha.net/api/getkey
See Installation for details.

== Installation ==

* Get your API keys from http://recaptcha.net/api/getkey.
* Edit bb-recaptcha.php to include these API keys.
* Upload 'bb-captcha' folder to 'my-plugins' folder inside your bbPress installation.

== License ==

Copyright (C) 2008, Dmitry Chestnykh  All rights reserved.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

== History ==

= Version 1.0 (2008-11-14) =

*   Initial release

== To Do ==

* Entering API keys from admin panel.
* Style reCAPTCHA and choose its language from admin panel.