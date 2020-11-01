@setlocal
@set path=c:\xampp\php;%path%
@rem c:\xampp\php\vendor\bin\phpunit test --testdox
c:\xampp\php\vendor\bin\phpunit tests %1 %2
@endlocal