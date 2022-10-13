## Documentation
- __[Configuration](CONFIGURATION.md)__
- [Use template variables](TEMPLATE_VARIABLES.md)
- [Extend and support other extensions](SUPPORT.md)
- [Import / Export](IMPORT_EXPORT.md)
- [Bundle-Configurations](BUNDLE_CONFIG.md)
- [Migration](docs/MIGRATE.md)

---

After the installation, a new navigation item "StyleManager" is displayed in the backend to create and edit the dataset of categories and CSS groups.

# Categories:

Categories can be considered as archives and form a logical separation of CSS groups. A special feature for the display in the backend, is the merging of these categories via the field `Group-Identifier`. This field can be filled freely to display categories with the same group identifier bundled as tab navigation in the widget.

| Field              | Description                                                                                                                  |
|--------------------|------------------------------------------------------------------------------------------------------------------------------|
| `Title`            | The title of the category.                                                                                                   |
| `Idenfifier`       | A unique value. This value must be used to retrieve the template variables.                                                  |
| `Group-Idenfifier` | In this field you can specify an alias that combines categories with the same alias and displays them as tabs in the widget. |
| `Sort-Index`       | This field is used to determine the order of the categories in the widget.                                                   |

### Example backend view of combined categories using `Group-Idenfifier`:
![Manage Categories: Image 3](https://www.oveleon.de/share/github-assets/contao-component-style-manager/2.0/combined-groups.png)

<br/>

# CSS-Groups:

If a category has been created, any CSS groups can be created within it. A CSS group represents the actual selection of selectable options that are made available to the editor for the various areas (articles, content elements, ...).

A special feature is that CSS groups can also be used as template variables via the `Use as template variable` field. In this case, the selected options are not automatically made available as a CSS class, but must be accepted manually in the template. Learn more about template variables.

| Field                      | Description                                                                                                                                                                                            |
|----------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `Alias`                    | A unique value. This value must be used to retrieve the template variables.                                                                                                                            |
| `Add search field`         | Activates the search within the widget for this CSS group. This setting is recommended only for a larger set of options.                                                                               |
| `Use as template variable` | This field defines the type of usage. When this field is checked, the variable is passed to the template instead of being used as a CSS class. [More about template variables](TEMPLATE_VARIABLES.md). |
| `CSS class`                | To further customize the display of the backend fields, you can specify a selection of predefined CSS classes. (long, clr, separator)                                                                  |
| `...`                      | All other fields should be self-explanatory                                                                                                                                                            |

### Example css group:
![Manage Groups: Image 1](https://www.oveleon.de/share/github-assets/contao-component-style-manager/2.0/groups-edit.png)
