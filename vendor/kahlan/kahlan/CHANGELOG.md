# Change Log

## Last changes

## 3.1.16 (2017-06-22)

  * **Bugfix:** Support 7.1 `void` return type.

## 3.1.15 (2017-05-26)

  * **Bugfix:** Update Kahlan's autoloader reference when patched.

## 3.1.14 (2017-04-12)

  * **Bugfix:** Fix inaccurate actually called times number in report error description messages.

## 3.1.13 (2017-04-12)

  * **Bugfix:** Fix a reporting issue which report errored specs as pending in some circumstances.

## 3.1.12 (2017-04-07)

  * **Bugfix:** Fix a monkey patching issue with curly braces namespace definitions.

## 3.1.11 (2017-04-06)

  * **Bugfix:** Add `clone()` to JIT ignored statements.
  * **Bugfix:** Fix a coverage reporting issue with global namespace definitions.

## 3.1.10 (2017-03-23)

  * **Add:** Enhance interoperability between frameworks.

## 3.1.9 (2017-03-19)

  * **Bugfix:** Fix exit/die short syntax patching.

## 3.1.8 (2017-02-14)

  * **Change:** Report specs with incomplete expectations as pending.

## 3.1.7 (2017-02-12)

  * **Bugfix:** Fix a coverage issue on windows.

## 3.1.6 (2017-01-30)

  * **Bugfix:** Fix wrongly group use declarations reported as coverable code (PHP>=7).

## 3.1.5 (2017-01-09)

  * **Bugfix:** Fix wrong reported logs introduced in 3.1.4.

## 3.1.4 (2017-01-09)

  * **Bugfix:** Fixes error catching in beforeAll()/afterAll().

## 3.1.3 (2017-01-06)

  * **Bugfix:** Parse Nowdoc syntax correctly.
  * **Bugfix:** Parses alternative control structures as dead code for code coverage.

## 3.1.2 (2016-12-29)

  * **Bugfix:** Fix some HHVM issues.

## 3.1.1 (2016-12-29)

  * **Bugfix:** Fix an issue with `given()` beeing regenerated when not expected.

## 3.1.0 (2016-12-13)

  * **BC break:** Remove the substitution feature (i.e allow(<something>)->toBe(<an instance>)) for PHP<7. It may generates some syntax errors since the uniform variable syntax is only supported by PHP>=7.

## 3.0.3 (2016-12-02)

  * **Add:** Support HHVM lambda through ==> syntax.

## 3.0.2 (2016-10-16)

  * **Change:** Parser identify interface's signatures as signatures and not function.
  * **Change:** Filter out files with no coverable code inside from coverage reporting.
  * **Bugfix:** Fix an issue on "travis" filesystem.

## 3.0.1 (2016-10-07)

  * **Bugfix:** Fix a couple of Windows issues.

## 3.0.0 (2016-09-01)

  * **Add:** Add `allow()` DSL.
  * **Add:** Add `toBeCalled()` matcher.
  * **Add:** `toReceive` can expect a chain of stubbed methods to be called.
  * **Add:** Monkey patching can patch all instances of a class to be a specific instance.
  * **Add:** Argument requirements can be applied on a chain of methods using `where()`.
  * **Add:** It's now possible to mark specs as pending by adding no expectation inside or mark them as excluded when using the `xit`, `xcontext`, `xdescribe` syntax.
  * **Change:** Refactor the reporting to provide more meaningful messages on failure.
  * **Bugfix:** Fix an issue with `toReceive()/toBeCalled` and stubs where past called methods were taken into account.
  * **BC break:** `Stub::on()` is now deprecated use `allow()` instead.
  * **BC break:** `Monkey::patch()` is now deprecated use `allow()` instead.
  * **BC break:** Rename `'params'` option to `'args'` in `Double::instance()`.
  * **BC break:** Rename `Stub::create()` to `Double::instance()`.
  * **BC break:** Rename `Stub::classname()` to `Double::classname()`.
  * **BC break:** Rename `before()` and `after()` to `beforeAll()` and `afterAll()`.
  * **BC break:** Rename `Args` to `CommandLine` (i.e. `$this->args()` become `$this->commandLine()` in `kahlan-config.php`)
  * **BC break:** Remove `toReceiveNext` matchers in flavor of `->ordered` attribute to be more close to Rspec way.
  * **BC break:** Refactor the reporting API.
  * **BC break:** Cached files are no more compatible, cached files needs to be purged.

## 2.5.8 (2016-09-29)

  * **Bugfix:** Ignore `declare()` statement from coverable statements.

## 2.5.7 (2016-09-23)

  * **BC break:** Moving Kahlan to its own organization.

## 2.5.6 (2016-08-18)

  * **Bugfix:** Allow passing 'string' as Stub's implements option.

## 2.5.5 (2016-08-13)

  * **Add:** Introduce the KAHLAN_VERSION constant.
  * **Add:** Better support of IDE though autocomplete.
  * **Add:** Implement call counting matcher on purpose.

## 2.5.4 (2016-06-15)

  * **Bugfix:** Fix naming collison conflicts with global functions.

## 2.5.3 (2016-05-10)

  * **Bugfix:** Fix a parsing issue when a class extends statement matches a use definition.

## 2.5.2 (2016-05-01)

  * **Bugfix:** Fix an issue related to a BC-break introduced by a composer optimization https://github.com/composer/composer/commit/fd2f51cea8e5f1ef978cd8f90b87b69dc1778976.

## 2.5.1 (2016-05-01)

  * **Bugfix:** Fix the release number.

## 2.5.0 (2016-04-27)

  * **Change:** Output the total coverage after per file coverage.
  * **Bugfix:** Skip specs when related extentions are not available.
  * **Bugfix:** Fix return types of non-builtins types in Stubs generation.
  * **BC break:** Exit and display an error message when `--coverage` is used but no driver are available.

## 2.4.1 (2016-03-23)

  * **Bugfix:** Fix stub generation for non-builtin PHP7 return types.

## 2.4.0 (2016-03-20)

  * **Add:** Add a JSON reporter.
  * **Add:** Add a TAP reporter.
  * **Add:** Allow to redirect reporter outputs to a file.
  * **BC break:** the `--reporter` option is now managed as an array.

## 2.3.2 (2016-02-18)

  * **Change:** Internal dependency container function refactoring.

## 2.3.1 (2016-02-13)

  * **Add:** Improve code coverage accuracy for unconsistant XDEBUG/PHPDBG code coverage result.

## 2.3.0 (2016-02-10)

  * **Add:** Support PHP7 variadic functions.
  * **Add:** Support PHP7 scalar typehints.

## 2.2.0 (2016-02-10)

  * **Add:** Support PHP7 return types.
  * **Add:** Support PHP7 group use declarations.
  * **Bugfix:** Fixes a Layer patcher issue when extends is not an absolute class name.

## 2.1.0 (2016-01-17)

  * **BC break:** Patcher can now be applied lazily.

## 2.0.1 (2015-12-09)

  * **Bugfix:** Fix a reporting issue related to the new repository structure.

## 2.0.0 (2015-12-05)

  * **BC break:** Use PascalCase conventions instead of lowercase for all namespaces.

## 1.3.0 (2015-12-05)

  * **Add:** Create a standalone version.
  * **Add:** Reintroduce PHP 5.4 support.
  * **BC break:** `use filter\Filter` must now be `use kahlan\filter\Filter` in `kahlan-config.php`.

## 1.2.11 (2015-11-24)

  * **Add:** Add `given()` function to set lazy loadable variables.

## 1.2.10 (2015-11-23)

  * **Add:** Allow Kahlan's binary to deal with custom composer.json `"vendor-dir"` config.

## 1.2.9 (2015-11-23)

  * **Bugfix:** Make sure Kahlan's global function can't be includes twice.

## 1.2.8 (2015-11-22)

  * **Bugfix:** Fix a cwd issue when installed globally.

## 1.2.7 (2015-11-07)

  * **Add:** Add a lcov compatible exporter.
  * **Bugfix:** Fixe a minor issue with the istanbul exporter.

## 1.2.6 (2015-11-07)

  * **Add:** Add an istanbul compatible exporter.

## 1.2.5 (2015-11-04)

  * **Add:** Restore IDE autocomplete feature for `expect()`.
  * **Bugfix:** Fixe `PointCut` patching with generators.

## 1.2.4 (2015-11-03)

  * **Bugfix:** Fix Kahlan's disable environment variable.

## 1.2.3 (2015-11-03)

  * **Add:** Allow to disable Kahlan functions by environment variable.
  * **Bugfix:** Fixes reported backtrace which was not accurate for deferred matchers.

## 1.2.2 (2015-10-22)

  * **Bugfix:** Fix `ToContainKey` when dealing with plain arrays and `null` values.

## 1.2.1 (2015-10-17)

  * **Bugfix:** Fix some Windows related issues.

## 1.2.0 (2015-10-13)

  * **Add:** Allow to set contextualized matchers.
  * **Add:** Introduce the `waitsFor` statement.
  * **BC break:** Drop PHP 5.4 support.
  * **BC break:** Internal classes has been refactored/renamed.

## 1.1.9 (2015-09-03)

  * **Bugfix:** Escape file path for coverage metrics.

## 1.1.8 (2015-07-30)

  * **Bugfix:** Fix an issue when stub needs to auto override methods where parameters are passed by reference.

## 1.1.7 (2015-07-27)

  * **Bugfix:** Fix a control structures issue when present in uppercase.

## 1.1.6 (2015-07-27)

  * **Bugfix:** Fix the order of `toContain()` matcher.

## 1.1.5 (2015-06-26)

  * **Add:** Add the `toContainKey()` matcher.
  * **Bugfix:** Monkey patching now supports `or`, `and` && `xor` alternative syntax.

## 1.1.4 (2015-06-04)

  * **Bugfix:** Make report backtrace more accurate on exceptions.

## 1.1.3 (2015-03-21)

  * **Add:** Remove composer minimum stability requirement.

## 1.1.2 (2015-03-20)

  * **Add:** Add the command line --cc option to clear the cache.
  * **Add:** Auto clear cache on "composer update".
  * **Add:** Add the command line --version option.
  * **Add:** Add `toMatchEcho` matcher.
  * **Bugfix:** Fix report duplication of some skip exceptions.
  * **Bugfix:** Reset `not` to false after any matcher call.

## 1.1.1 (2015-03-17)

  * **Bugfix:** Fix a double open tag issue with the `Layer` patcher.
  * **Bugfix:** Fix missing pointcut patching in the `Layer` patcher.

## 1.1.0 (2015-02-25)

  * **Add:** Allow Stubs to override all public method of their parent class by setting the `'layer'` option to `true`.
  * **Add:** Introduce the `Layer` proxy to be able to stub methods inherited from PHP core method.
  * **Change:** The look & feel of reporters has been modified.
  * **Bugfix:** Add a default value for stubbed function parameters only when exists.
  * **Bugfix:** Return absolute namespace for typehint
  * **Bugfix:** Generalize method overriding with stubs.
  * **BC break:** The Stubs `'params'` option now identifies each values to pass to `__construct()`.
  * **BC break:** Reporter's hooks has been renamed and now receive a report instance as parameter instead of an array.

## 1.0.6 (2015-02-11)

  * **Add:** Implement missing Jasmine `expect(string)->toContain(substring)` behavior.
  * **Change:** Allow arguments to also be set in kahlan config files.
  * **Bugfix:** Fix Monkey patcher when some patchable code are outside namespaces/classes or functions.

## 1.0.5 (2015-02-10)

  * **Bugfix:** Resolve default cache path (based on`sys_get_temp_dir()`) to be a real dir on OS X.

## 1.0.4 (2015-02-03)

  * **Deprecate:** Feprecate ddescribe/ccontext/iit in flavor of fdescribe/fcontext/fit (Jasmine 2.x naming)

## 1.0.3 (2015-02-02)

  * **Bugfix:** Fix `use` statement patching for partial namespace

## 1.0.2 (2015-02-01)

  * **Change:** The terminal reporter displaying has been modified
  * **Bugfix:** Fix code coverage driver to make it work with HHVM
  * **BC break:** The `'autoloader'` filter entry point has been renamed to `'interceptor'`

## 1.0.1 (2015-01-28)

  * **Add:** New reporter `--reporter=verbose`

## 1.0.0 (2015-01-24)

  * Initial Stable Release
