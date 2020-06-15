# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]


## [2.0.2] - 2020-06-15
### Fixed
* Prevent method `getUniquePlaceholder()` hash from generating strings with a `dot` in it which will break doctrine query builder

## [2.0.1] - 2020-03-30
### Fixed
* Fix `getPaginationLimit()` getter when no pagination was given by the user

## [2.0.0] - 2020-03-20
### Added
* Support for Symfony 5.0
* PHP quality checker to this project

### Changed
* Doctrine ORM to be an optional dependency
* `ApiToolkitRepository` renamed to `ApiToolkitServiceRepository` 
* `ApiToolkitDefaultRepository` renamed to `ApiToolkitRepository` 


### Removed
* __support for symfony < 4.3__

### Fixed


## [1.0.15] - 2020-03-09
Last release without a changelog ;-) 
 
