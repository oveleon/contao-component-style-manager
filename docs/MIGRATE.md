## Migration

#### Migrate from version 2 to 3
Bundle configurations were added with version 3. This makes it necessary to migrate the StyleManager dataset. If only standard Contao tables were used and the StyleManager was not added to other / own database tables, calling the install tool is sufficient.

If the StyleManager was used for other / own database tables, these tables must be migrated manually using the following command:

```shell
$ php contao-console contao:stylemanager:object-conversion tl_mytable
```
