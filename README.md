## Toolbar Theme Switcher — for WordPress ##
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Rarst/toolbar-theme-switcher/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Rarst/toolbar-theme-switcher/?branch=master)

Plugin provides toolbar (admin bar) menu to quickly switch between available themes.

Theme choice is individual for user, saved in cookies and doesn't affect current theme of the site.

Plugin is multisite-aware — it will only list themes allowed for site and save choice for each site separately.

## Installation ##

Download plugin archive from [releases section](https://github.com/Rarst/toolbar-theme-switcher/releases).

Or install in plugin directory via [Composer](https://getcomposer.org/):

    composer create-project rarst/toolbar-theme-switcher --no-dev

## Frequently Asked Questions ##

### I switched to a broken theme!

You can reset via special URL parameter `example.com?tts_reset` or by clearing browser’s cookies for the site. 

### I don't want all themes to show...

Filter `tts_allowed_themes` and unset unwanted themes.

Example code (removes Twenty Ten theme from the list):

```php
add_filter( 'tts_allowed_themes', 'hide_twenty_ten' );

function hide_twenty_ten( $themes ) {

	unset( $themes['Twenty Ten'] );

	return $themes;
}
```

### Who can see and use the menu?

Users with `switch_themes` capability (administrators by default).

Filter `tts_capability` (capability name) or `tts_can_switch_themes` (boolean) to customize.

### I don't want theme name in toolbar? | I want something else in toolbar?

Filter `tts_root_title` to control what it says.

### I have a lot of themes and it's becoming sluggish... 

Plugin needs to build full list of available themes, which isn't typically needed on each page load and can be relatively slow with a lot of disk access.
 
When using Object Cache, caching theme data in WordPress can be enabled via:

```php
add_filter( 'wp_cache_themes_persistently', '__return_true' );
```

Since it doesn't handle invalidation (will need to wait or flush cache when themes are added/removed), plugin isn't enabling it and leaves choice up to the user.