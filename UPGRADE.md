# Upgrade from StyleManager Version 3.* to 4.0

* Minimum PHP-Version is 8.4
* The built-in XML-Import has been removed.
* The built-in XML-Export has been removed.
* The built-in partial XML-Export has been removed.
* The built-in partial XML-Import has been removed.
* The `objectConversion` command has been removed.
* The `ObjectConversion` migration has been removed.
* The `styleManagerFindByTable` hook has been removed, use the event instead.
* The `styleManagerIsVisibleGroup` hook has been removed, use the event instead.
* The `styleManagerSkipField` hook has been removed, use the event instead.
* The `styleManagerFormatMethod` hook has been removed.
* The `styleManagerGroupFieldOptions` hook has been removed.
* Whilst XML bundle-configurations still work, you are encouraged to use the YAML configuration.
* The config option `show_group_title` has been removed. Hide the group titles by extending the Twig template.
* The config option `use_bundle_config` has been removed. Bundle configurations are now loaded by default.
* Bundle configurations are not loaded using the `StyleManagerArchive` and `StyleManager` Model anymore.
