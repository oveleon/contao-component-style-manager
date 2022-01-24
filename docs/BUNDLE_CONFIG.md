## Bundle-Configurations
Instead of the import function, from version 3, configurations can be automatically provided by other bundles.  For the deployment, a configuration file, which can be exported via the StyleManager, must be stored under `contao/templates` of the bundle. If the automatic import of these configuration files is not prevented (allowed by default), archives and CSS groups are automatically added to the defined areas.

To prevent dynamic configurations from being read in, the following lines must be added to the config.yml file:
```yaml
contao_component_style_manager:
    use_bundle_config: false
```

