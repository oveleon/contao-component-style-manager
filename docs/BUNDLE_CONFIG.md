## Documentation
- [Configuration](CONFIGURATION.md)
- [Use template variables](TEMPLATE_VARIABLES.md)
- [Support own extensions](SUPPORT.md)
- [Import / Export](IMPORT_EXPORT.md)
- __[Bundle-Configurations](BUNDLE_CONFIG.md)__
- [Migration](docs/MIGRATE.md)

---

# Bundle-Configurations
Instead of the import function, from version 3, configurations can be automatically provided by other bundles.  For the deployment, a configuration file, which can be exported via the StyleManager, must be stored under `contao/templates` of the bundle. The file needs to start with `style-manager-`. If the automatic import of these configuration files is not prevented (allowed by default), archives and CSS groups are automatically added to the defined areas.

To prevent dynamic configurations from being read in, you can make the following configuration:
```yaml
# config.yaml
contao_component_style_manager:
    use_bundle_config: false
```

