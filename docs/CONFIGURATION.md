# Manage Categories:

| Field              | Description                                                                                                                  |
|--------------------|------------------------------------------------------------------------------------------------------------------------------|
| `Title`            | The title of the category.                                                                                                   |
| `Idenfifier`       | A unique value. This value must be used to retrieve the template variables.                                                  |
| `Group-Idenfifier` | In this field you can specify an alias that combines categories with the same alias and displays them as tabs in the widget. |
| `Sort-Index`       | This field is used to determine the order of the categories in the widget.                                                   |

#### Example backend view of combined categories using `Group-Idenfifier`:
![Manage Categories: Image 3](https://www.oveleon.de/share/github-assets/contao-component-style-manager/2.0/combined-groups.png)

<br/>

# Manage CSS-Groups:
### Fields:
| Field                      | Description                                                                                                                                                                                         |
|----------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `Alias`                    | A unique value. This value must be used to retrieve the template variables.                                                                                                                         |
| `Add search field`         | Activates the search within the widget for this CSS group. This setting is recommended only for a larger set of options.                                                                            |
| `Use as template variable` | This field defines the type of usage. When this field is checked, the variable is passed to the template instead of being used as a CSS class. [More about template variables](TEMPLATE_VARIABLES). |
| `CSS class`                | To further customize the display of the backend fields, you can specify a selection of predefined CSS classes. (long, clr, separator)                                                               |
| `...`                      | All other fields should be self-explanatory                                                                                                                                                         |

#### Example css group:
![Manage Groups: Image 1](https://www.oveleon.de/share/github-assets/contao-component-style-manager/2.0/groups-edit.png)
