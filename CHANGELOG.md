# Changelog
All notable changes to this project will be documented in this file.

## [Unreleased]

## [1.0.0] - 2019-02-28
- Initial Release

## [1.0.1] - 2019-03-02
- Updated the composer.json to include license info, and pointed the main README.md to the new documentation site.

## [1.1.0] - 2019-03-03
- Added the new `synchronous` and `asynchronous` methods to the `Expectations` class.
- Updated brand slogan to "Supercharge your unit tests that use Guzzle with a mock-like syntax."

## [1.2.0] - 2019-03-16
- Added the new `withFormField` and `withForm` methods to the `Expectations` class.
- Added the new `withJson` method to the `Expectations` class.

## [1.2.1] - 2019-03-23
- Refactored filters into class based structure so that any number of filters can be added in the future.
- Added `Disposition` class to parse multipart form post bodies.
- Refactored `WithForm` class to work with both URL encoded and multipart forms.
- Refactored `withJson` functionality to be more in-line with most other filters. Now, by default, the expectation returns true if the body contains a stringified version of the passed argument. A new `$exclusive` boolean value can be passed as the second argument to return to the previous exact match nature. The new signature is `withJson(array $json, bool $exclusive = false)`.
- Refactored `withBody` functionality to be more in-line with most other filters. Now, by default, the expectation returns true if the body contains the passed argument. A new `$exclusive` boolean value can be passed as the second argument to return to the previous exact match nature. The new signature is `withBody(string $body, bool $exclusive = false)`.  