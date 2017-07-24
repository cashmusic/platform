# Contribution Guidelines
You are welcome to report [issues](https://github.com/chadicus/slim-oauth2-routes/issues) or submit [pull requests](https://github.com/chadicus/slim-oauth2-routes/pulls).  While the below guidelines are necessary to get code merged, you can submit pull requests that do not adhere to them and I will try to take care of them in my spare time. If you can make sure the build is passing 100%, that would be very useful.

I recommend including details of your particular usecase(s) with any issues or pull requests.

## Questions and Bug Reports
Submit via [GitHub Issues](https://github.com/chadicus/slim-oauth2-routes/issues).

## Pull Requests
Code changes should be sent through [GitHub Pull Requests](https://github.com/chadicus/slim-oauth2-routes/pulls).  Before submitting the pull request, make sure that phpunit reports success:

```sh
./vendor/bin/phpunit --coverage-html coverage
```

While the build does not enforce 100% [PHPUnit](http://www.phpunit.de) code coverage, it will not allow coverage to drop below its current percentage.

The build will also not allow any errors for the [coding standard](http://chadicus.github.io/coding-standard/)

```sh
./vendor/bin/phpcs --standard=./vendor/chadicus/coding-standard/Chadicus src tests
```
