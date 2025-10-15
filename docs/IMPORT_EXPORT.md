## Documentation
- [Configuration](CONFIGURATION.md)
- [Use template variables](TEMPLATE_VARIABLES.md)
- [Support own extensions](SUPPORT.md)
- __[Import / Export](IMPORT_EXPORT.md)__
- [Bundle-Configurations](BUNDLE_CONFIG.md)
- [Migration](docs/MIGRATE.md)

---

> [!WARNING]
> **The XML Import feature has been deprecated as of version 3.11 and will be removed in the future**.
> See [Bundle-Configurations](BUNDLE_CONFIG.md) for using the bundle configuration instead.

# Import / Export
To fill projects with a default setting, the Import and Export functions are available.

When importing, the categories as well as the CSS groups are only added additively. This allows CSS classes to be added to the actual project without being deleted after an import.

> Please note that the import completes the records by the identifier (categories) and the alias (CSS groups). So if the aliases are changed in the current project, they are not overwritten / added, but a new group is created after the import.

### Prepare dynamic configurations
See also: [Bundle-Configurations](BUNDLE_CONFIG.md)
