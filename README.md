Magister
========

Magister is a programmer oriented framework for building complex made-to-order 
application. Its modus operandi is to provide tools to facilitate and speed up 
website and application development, and stay out of the way.

It is meant to be a lightweight framework, for projects that to not need or want 
the bulk of frameworks such as CakePHP, Symfony, Zend, etc. It does however, use 
concepts similar to these frameworks, such as Convention over Configuration and 
a MVC-like system.

Magister makes no assumptions as to the type of application that is running on 
it, so you are free to build your application as you want.

Installation
------------
The simplest installation is to upload the package to your server and set the 
`DocumentRoot` to the path of the app directory. If that is not possible, the 
whole package can be uploaded straight into the current `DocumentRoot`.

The advanced installation consists of uploading the `lib` directory to a central 
location on your server, outside of the `DocumentRoot`, and the app to the 
`DocumentRoot` (or a location within). This method requires editing the value of 
the `LIB_DIR` constant in the application's `index.php` file to point to the 
directory containing the `Magister` folder. This method allows multiple apps to 
share the same library.
