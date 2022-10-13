# Version 2.* to 3.0
## Storage adjustment
### Migration
If you update from version 2 to version 3, you have to perform a migration, otherwise the records will break and cannot be reassigned in the StyleManager widget. If the StyleManager widget is already called before running a migration, selected properties are inserted into the DCA's CSS class field to avoid errors.

[Learn more about migration](docs/MIGRATE.md)

## Restructure of the Bundle
### Namespaces
Due to the restructuring of the bundle, the namespaces have to be adjusted, if the StyleManager was used for vendor DCAs.

[Learn more about StyleManager Support](docs/SUPPORT.md)
