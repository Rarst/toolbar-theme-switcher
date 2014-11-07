# Changelog

## 1.3
* _(enhancement)_ made plugin not interfere with Appearance admin page and real theme switching

## 1.2.1
* _(internal)_ updated theme allowed check to use native method over enumeration, improves boot performance

## 1.2
* _(internal)_ reworked to filter underlying options - more robust, should solve customizer issues and similar
* _(localization)_ made translation-ready, added russian translation
* _(compatibility)_ removed legacy checks, increased minimum required WordPress version to 3.4

## 1.1.4
* _(bugfix)_ fixed crash in installations with multiple theme directories

## 1.1.3
* _(docs)_ added example code for removing theme from list to readme
* _(bugfix)_ improved cookie name to better work on secure sites

## 1.1.2
* _(bugfix)_ changed order of function checks to prevent deprecated notices in admin

## 1.1.1
* _(bugfix)_ fixed fatal error on deprecated function in WP 3.4 #blamenacin
* _(internal)_ switched to older `add_menu()` method for now (pre-3.3 compatibility)

## 1.1
* _(internal)_ code cleanup
* _(enhancement)_ show current theme name in toolbar, props Leho Kraav
* _(enhancement)_ added filter for root node title, props Leho Kraav

## 1.0
* Initial repository release.