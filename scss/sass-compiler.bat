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