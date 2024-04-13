# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## \[In Development\] - Unreleased

## \[1.6.1\] - 2024-04-13

### Changed

- Translations updated

## \[1.6.0\] - 2024-04-13

### Removed

- `vendor` directory from the plugin again, as its no longer needed

## \[1.5.2\] - 2024-03-12

### Changed

- Translations updated

## \[1.5.1\] - 2024-03-08

### Added

- Apparently, we have to add the whole vendor directory to the plugin for the
  autoloader to work properly. This is a bit of a pain, but seems to be the only way.

## \[1.5.0\] - 2024-03-01

### Changed

- Using [strauss](https://github.com/BrianHenryIE/strauss) to include our dependencies in the plugin's namespace

## \[1.4.1\] - 2024-02-23

### Changed

- Updates to linting and automated code style checks

## \[1.4.0\] - 2024-02-19

### Added

- Memory usage information to the admin bar

## \[1.3.1\] - 2023-10-03

### Changed

- Available translations updated

## \[1.3.0\] - 2023-09-15

### Fixed

- Plugin namespace

## \[1.2.0\] - 2023-09-15

### Changed

- Integrate PluginUpdateChecker into our namespace

### Removed

- Redundant argument

## \[1.1.0\] - 2023-09-12

### Added

- New GitHub updater library for hopefully better results
- pre-commit checks
- GH workflows for pre-commit checks
- Minimum Requirements to README
  - WP 6.0
  - PHP 8.2
