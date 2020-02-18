# amp-stack-blog-template
A customizable template to make any type of blog. Built on the AMP stack and Bootstrap.

![Example](https://i.imgur.com/FdLHhwo.png)

<h1>Installing</h1>

<h3>Create database schema</h3>

Submit the form on "sql/index"

<h3>Setup mail()</h3>

https://stackoverflow.com/questions/4095289/how-to-configure-php-ini-to-use-gmail-as-mail-server

<h3>Prepare sass compiler (skip this if your code editor can compile sass)</h3>

<h4>1. Install/upgrade node and NPM</h4>

https://github.com/felixrieseberg/npm-windows-upgrade

<h4>2. Install/upgrade npx</h4>
<code>
  
    npm install -g npx

    Install postcss and autofixer:
    npm install postcss-cli autoprefixer
</code>
https://github.com/postcss/postcss-cli

<h4>3. Download/Build libsass and sassc</h4>

https://github.com/sass/libsass/blob/master/docs/build-with-visual-studio.md
https://github.com/sass/sassc/blob/master/docs/building/windows-instructions.md

<h4>4. Finish setting up sassc</h4>
Copy sassc.exe into scss directory and create sass-compiler.bat...

<code>
  
    @echo off
    set RESTVAR=
    shift
    :loop1
    if [%3]==[] goto after_loop
    set RESTVAR=%RESTVAR% %3
    shift
    goto loop1
    
    :after_loop
    echo %RESTVAR%
    echo "args above"
    echo "running"
    
    C:\libsass\sassc\sassc.exe %RESTVAR%
    
    postcss "css\*.css" --replace --use C:\Windows\System32\node_modules\autoprefixer
</code>
<h3>Configure httpd.conf</h3>
<code>
  
    Disable "socache_shmcb_module"
    Disable "pagespeed"
</code>
<h3>Configure php.ini</h3>
<code>
  
    include_path = "...[ROOT DIRECTORY]"
    opcache.enable=0
</code>
<h1>DEVELOPMENT</h1>

Follow the TODO instructions in the following files:

 - .htaccess
 - utilities.php
 - error-codes.php
 - Modal.php
 - RegEx.php
 - flags.php
 - UserPowerLevels.php
 - emails/email-verification.php
 - emails/password-change.php
 - _pdo.php
