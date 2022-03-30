# KyuWebServe

KyuWebServe is a [KyuWeb](https://github.com/GarrettAlbright/KyuWeb) server. It, much like KyuWeb itself, is still very early in progress, but it should be functional.

## Installation

KyuWebServe requires a web server configured to run PHP scripts. PHP 7.4 or later is expected (KyuWebServe may run with older versions of PHP but that has not been tested). Setting up such a server is outside the scope of this web document. For testing purposes, [PHP's built-in server](https://www.php.net/manual/en/features.commandline.webserver.php) will suffice.

Additionally, [Composer](https://getcomposer.org) is required for installation.

1. Clone this repository into a useful place using git, or download an archive and extract its contents to a useful place.
2. Inside the directory, run `composer install` to download dependencies.
3. In the main level of the directory, create a "doc" directory. Make sure it is readable by the user that the PHP process will run as.
  - The Markdown documents that your site will serve will go in this directory.
  - When a directory is requested rather than a specific document name, a file with the name of "index.md" will be served. For example, if "http://example.com/asdf" is requested and "doc/asdf" (relative to the root level of KyuWebServe) is a directory, KyuWebServe will attempt to serve "doc/asdf/index.md".
  - The contents of this directory may be kept in its own version control repository.
  - If you don't yet have any documents you want to serve, create a file named "index.md" for testing purposes: `echo '# Hello World!' > doc/index.md`
4. Configure your web server of choice to route all requests for the site to the "public/index.php" file. The specifics for this will vary depending on the server daemon you're using, so consult its documentation for details. If you just want to use the built-in PHP server for testing, give the path to "index.php" as a parameter after the server name and port; eg `php -S localhost:8888 index.php`
5. Direct your web browser or KyuWeb browser to the domain name, port, and path (if necessary) as you configured.

Note that a KyuWeb browser does not actually exist as of the writing of this document. You can use Curl with specified headers to emulate the hypothetical behavior of such a browser: `curl 'localhost:8080' -H 'Accept-KyuWeb: 0.1' -H 'Accept: text/markdown'`

## To Do

In rough order of expected implementation date.

- Better templates for HTML-wrapped Markdown documents, including JavaScript for rendering the Markdown to HTML.
- Implement a working KyuWeb browser.
- User-overridable templates.
- Allow for specifying a different/custom directory for documents and for user templates.
