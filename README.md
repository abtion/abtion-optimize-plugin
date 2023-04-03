# Abtion Optimize - WP Plugin

This plugin removes unused scripts and styles from existing WordPress sites. It also monitors
the current "Site Health" score, and reports to service@abtion.com if any attention is needed.
The plugin will not interfere with any content or theme-files, and therefore it will **not** cause any
errors on an existing WordPress site.

# Supported WordPress versions

This plugin will work on WordPress 5.2 and onward  
It will not cause any issues on prior versions, but "Site Health" was not introduced until 5.2

# Changelog

- 1.2.1 - Disable SSL check and GTMetrix check for test-domains (TLD = '.test')
- 1.2 - Checking perormance on GTMetrix, using Abtion's API
- 1.1 - Checking SSL expiry-date - if less than 40 days, a warning will be sent to service-desk.
- 1.0 - Testing a WordPress' sites "health" by using the buil-in health-checker.

