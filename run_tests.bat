@setlocal
@set path=d:\xampp\php;%path%
@rem c:\xampp\php\vendor\bin\phpunit test --testdox
d:\xampp\php\vendor\bin\phpunit tests %1 %2 %3
@endlocal