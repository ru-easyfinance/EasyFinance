===================================
EXTENSION INSTALLATION INSTRUCTIONS
===================================

What is this?
=============

SignOutRedirect is a little extension to redirect you to a specific page rather 
than displaying a successfull logout message (with link to login page).

Configuration
=============

You may configure the url to redirect to via your settings.php by adding

     $Configuration['SIGNOUT_REDIRECT_URL'] = '<target>'

where <target> is an absolute url starting with http, like http://niflheim.de or
a page name inside vanilla like index.php, categories.php, ... Default is to use
index.php.

Just activate the extension and it takes effect.

Installation 
============

In order for Vanilla to recognize an extension, it must be contained within its
own directory within the extensions directory. So, once you have downloaded and
unzipped the extension files, you can then place the folder containing the
default.php file into your installation of Vanilla. The path to your extension's
default.php file should look like this:

/path/to/vanilla/extensions/SignOutRedirect/default.php

Once this is complete, you can enable the extension through the "Manage
Extensions" form on the settings tab in Vanilla.
