# Changelog
All notable changes to this project will be documented in this file.

## [2.3.0] - 2025-07-05
- Add support for PHPUnit 12, and PHP 8.4  Current version support is shown in table below.

| PHP | PHPUnit         |
|-----|-----------------|
| 8.1 | 9.6, 10         |
| 8.2 | 9.6, 10, 11     |
| 8.3 | 9.6, 10, 11     |
| 8.4 | 9.6, 10, 11, 12 |

## [2.2.0] - 2024-02-19
- Add support back for PHPUnit 9.6 and PHP 8.1. Now includes support for the following combinations:

| PHP | PHPUnit     |
|-----|-------------|
| 8.1 | 9.6, 10     |
| 8.2 | 9.6, 10, 11 |
| 8.3 | 9.6, 10, 11 |

## [2.2.0] - 2025-07-05
- Add support for PHPUnit 12 and PHP 8.4

## [2.1.2] - 2024-02-03
- Update to support PHPUnit 11
- Fix type hinting in macros to support both Guzzler and Hybrid use at once

## [2.1.0] - 2023-07-14
- Changes support to PHPUnit 10. This version change required which classes within PHPUnit are used. As such, any apps still using PHPUnit 9 and below should lock to 2.0.3
- Dropping support for PHP 8.0, as PHPUnit 10 also dropped support for it.

## [2.0.3] - 2022-12-27
- Add support for PHP 8.2, remove support for 7.4
- Force update to Guzzle version 7.4.3 to handle security issue.
- Update version of Chassis, underlying engine from BlastCloud

## [2.0.2] - 2022-01-17
- Adding a new filter `withRpc`, to ensure the request properly fits the JSONRPC spec.
  - Thanks `@webdevium` for the pull request.
- Testing on PHP 8.1
- Drops support for PHP versions below 7.4

## [2.0.1] - 2020-12-04
- Testing on PHP 8.0
- Moving unit testing to Github Actions

## [2.0.0] - 2020-06-28
- Updated to support Guzzle 7

## [1.6.1] - 2020-03-09
- Update to support PHPUnit 9
- Drop support for PHPUnit below 8.2
- Drop support for PHP 7.1

## [1.6.0] - 2020-01-10
- Updating CI to test on PHP 7.4
  - This will be the last release supporting PHP 7.1
- Added new methods: withoutQuery, withQueryKey, and withQueryKeys

## [1.5.3] - 2019-10-03
- Moved codebase to build on `blastcloud/chassis`. [Chassis](https://github.com/blastcloud/guzzler) is the abstracted expectation engine that was originally built for Guzzler. Now, Chassis can be used as a common base for any number of testing libraries for different PHP HTTP request clients. Check out [Hybrid](https://hybrid.guzzler.dev), a port of Guzzler for Symfony's HttpClient component.
- Added the ability to rename the engine in test files if desired. You no longer have to have the engine named `guzzler` if you'd rather it be named something else. See the [docs here](https://guzzler.dev/getting-started/#custom-engine-name).

## [1.5.2] - 2019-06-10
- Fixed the deprecated/removed `ObjectInvocation` in PHPUnit 8.2 and above installations.
  - Thanks `@llstarscreamll` for reporting the bug.

## [Documentation ] Roadmap - 2019-06-06
- Published the official road map for the future of the project.
- Created associated Github issues for user feedback.
    - Thanks `@jdreesen` for fixing incorrect syntax in the `Drive` proposal.

## [1.5.1] - 2019-05-20
- Added the ability to add a custom error message for `withCallback`.
- Fixed the `json_encode` formatting for the error message on `withQuery`.
- Various updates to the documentation. 

## [1.5.0] - 2019-05-04
- Added the `withFile` filter. Includes several new tests and a `File` helper object.
- Added the `withCallback` filter which allows users to pass an arbitrary anonymous function to filter history items.

## [1.4.2] - 2019-04-21
- Fix to move macros file loaded via composer. Includes test to ensure provided macros can be overridden.

## [1.4.1] - 2019-04-04
- Fix for possibility that Guzzler macros were not loaded because the extension may not be added to a project that was pre-existing.

## [1.4.0] - 2019-04-04
- Added the ability to add custom `macro`s.
- Added an `extension` class that can be added to a `phpunit.xml` file to globally load both a custom filter namespace and a macros file.

## [1.3.0] - 2019-03-24
- Added `Exposition::addNamespace` method to allow users to write custom filters and override Guzzler provided filters.
- Refactored `Exposition::__call` method to search through any user-provided namespaces for filters before using the defaults provided by Guzzler.

## [1.2.1] - 2019-03-23
- Refactored filters into class based structure so that any number of filters can be added in the future.
- Added `Disposition` class to parse multipart form post bodies.
- Refactored `WithForm` class to work with both URL encoded and multipart forms.
- Refactored `withJson` functionality to be more in-line with most other filters. Now, by default, the expectation returns true if the body contains a stringified version of the passed argument. A new `$exclusive` boolean value can be passed as the second argument to return to the previous exact match nature. The new signature is `withJson(array $json, bool $exclusive = false)`.
- Refactored `withBody` functionality to be more in-line with most other filters. Now, by default, the expectation returns true if the body contains the passed argument. A new `$exclusive` boolean value can be passed as the second argument to return to the previous exact match nature. The new signature is `withBody(string $body, bool $exclusive = false)`.

## [1.2.0] - 2019-03-16
- Added the new `withFormField` and `withForm` methods to the `Expectations` class.
- Added the new `withJson` method to the `Expectations` class.
    - Thanks `@satoved` for the idea and request.

## [1.1.0] - 2019-03-03
- Added the new `synchronous` and `asynchronous` methods to the `Expectations` class.
- Updated brand slogan to "Supercharge your unit tests that use Guzzle with a mock-like syntax."

## [1.0.1] - 2019-03-02
- Updated the composer.json to include license info, and pointed the main README.md to the new documentation site.

## [1.0.0] - 2019-02-28
- Initial Release
