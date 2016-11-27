# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) 
and this project adheres to [Semantic Versioning](http://semver.org/).

## 1.5 - 2016-11-27

### Added
- made core's Twenty themes sorted by year in list

### Changed
- made list skip current theme.
- moved main class to a separate file

## 1.4 - 2014-11-12

### Added
- implemented reset link for simpler clearing of cookie

## 1.3 - 2014-09-08

### Added
- made plugin not interfere with Appearance admin page and real theme switching

## 1.2.1 - 2013-05-16

### Changed
- updated theme allowed check to use native method over enumeration, improves boot performance

## 1.2 - 2013-03-05

### Added
- made translation-ready, added russian translation

### Changed
- reworked to filter underlying options - more robust, should solve customizer issues and similar
- removed legacy checks, increased minimum required WordPress version to 3.4

## 1.1.4 - 2013-01-04

### Fixed
- fixed crash in installations with multiple theme directories

## 1.1.3 - 2012-12-04

### Added
- added example code for removing theme from list to readme

### Fixed
- improved cookie name to better work on secure sites

## 1.1.2 - 2012-12-04

### Fixed
- changed order of function checks to prevent deprecated notices in admin

## 1.1.1

### Fixed
- fixed fatal error on deprecated function in WP 3.4 #blamenacin

### Changed
- switched to older `add_menu()` method for now (pre-3.3 compatibility)

## 1.1

### Added
- show current theme name in toolbar, props Leho Kraav
- added filter for root node title, props Leho Kraav

### Changed
- code cleanup

## 1.0

### Added
- Initial repository release.