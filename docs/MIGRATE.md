## Documentation
- [Configuration](CONFIGURATION.md)
- [Use template variables](TEMPLATE_VARIABLES.md)
- [Extend and support other extensions](SUPPORT.md)
- [Import / Export](IMPORT_EXPORT.md)
- [Bundle-Configurations](BUNDLE_CONFIG.md)
- __[Migration](docs/MIGRATE.md)__

---

# Migration

#### Migrate from version 2 to 3
Bundle configurations were added with version 3. This makes it necessary to migrate the StyleManager dataset. If only standard Contao tables were used and the StyleManager was not added to other / own database tables, calling the install tool is sufficient. Starting with Contao 5, the migration has to be confirmed manually by the Contao Manager. 

If the StyleManager was used for other / own database tables, these tables must be migrated manually using the following command:

```shell
# vendor/bin/
$ php contao-console contao:stylemanager:object-conversion tl_mytable
```
