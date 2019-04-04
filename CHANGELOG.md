# Changelog
All notable changes to this project will be documented in this file.

## [Unreleased]
- Currently in the process of writing a `WithFile` filter. As might be expected, there are lots of gotchas associated with file work. Hope to have this one out in another release some time in the next week or two. (Now being 2019-03-24)
- I also plan to write and release a road map document on the docs site outlining where I want to go with the next project, tentatively called `Drive`, that will work with Guzzler. The short description of it is, "Response factories based on Swagger, RAML, or API Blueprint docs".

## [1.4.1] - 2019-04-04
- Fix for possibility that Guzzler macros were not loaded because the extension may not be added to a project that was pre-existing.

## [1.4.0] - Released 2019-04-04
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

## [1.1.0] - 2019-03-03
- Added the new `synchronous` and `asynchronous` methods to the `Expectations` class.
- Updated brand slogan to "Supercharge your unit tests that use Guzzle with a mock-like syntax."

## [1.0.1] - 2019-03-02
- Updated the composer.json to include license info, and pointed the main README.md to the new documentation site.

## [1.0.0] - 2019-02-28
- Initial Release