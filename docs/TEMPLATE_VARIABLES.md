## Documentation
- [Configuration](CONFIGURATION.md)
- __[Use template variables](TEMPLATE_VARIABLES.md)__
- [Support own extensions](SUPPORT.md)
- [Import / Export](IMPORT_EXPORT.md)
- [Bundle-Configurations](BUNDLE_CONFIG.md)
- [Migration](docs/MIGRATE.md)

---

# Passing css group variables to a template:
If the checkbox `Use as template variable` is set, these are not automatically passed to the CSS class of the corresponding element but are available in the template.
To access the variables, we can access the corresponding class collection via the `styleManager` object.

![Passing Variables: Image 1](https://www.oveleon.de/share/github-assets/contao-component-style-manager/2.0/template-vars-list.png)

## API:
### 🔹 `get`
Return selected CSS classes of a category or a specific group

__Method arguments:__

| Argument          | Type           | Description         |
|-------------------|----------------|---------------------|
| identifier        | `string`       | Category identifier |
| groups (Optional) | `null׀array`   | Group aliases       |

__Examples:__
```php
// Return of all selected CSS classes of a category
$this->styleManager->get('myCategoryIdentifier');

// Return of all selected CSS classes in specific groups of a category
$this->styleManager->get('myCategoryIdentifier', ['alias1', 'alias2']);
```
Within a Twig template:
```twig
{# Return of all selected CSS classes of a category within a class attribute #}
{{ styleManager(data).get('myCategoryIdentifier') }}

{# Return of all selected CSS classes in specific groups of a category #}
{{ styleManager(data).get('myCategoryIdentifier', ['alias1', 'alias2']) }}
```
You can also use the Twig addClass funktions to pass a StyleManager variable into a Twig variable:
```twig
{# Adding the value of the 'headline-font-size' variable which is part of the category identifier 'general' to the 'headline' Twig variable within the _headline.html.twig template #}
{% set headline = headline|merge({attributes: attrs()addClass(styleManager(data).get('general', ['headline-font-size'])).mergeWith(headline.attributes|default)}) %}
```

### 🔹 `prepare` + `format`
Different from the `get` method, you can specify your own output format and a predefined or custom method to validate the output.

__Arguments of the `prepare` method:__

| Argument          | Type           | Description         |
|-------------------|----------------|---------------------|
| identifier        | `string`       | Category identifier |
| groups (Optional) | `null׀array`   | Group aliases       |

__Arguments of the `format` method:__

| Argument          | Type     | Description                                                                                                                                 |
|-------------------|----------|---------------------------------------------------------------------------------------------------------------------------------------------|
| format            | `string` | The format parameter must contain a format string valid for `sprintf` (PHP: [sprintf](https://www.php.net/manual/de/function.sprintf.php))) |
| method (Optional) | `string` | A method name to manipulate the output                                                                                                      |

__Examples:__
```php
// Return of all selected CSS classes of a category within a class attribute
$this->styleManager->prepare('myCategoryIdentifier')->format('class="%s"');

// Additional classes are often appended to an existing class attribute. In this case, unnecessary if-else statements can be avoided by appending a space character if a value exists.
$this->styleManager->prepare('myCategoryIdentifier')->format(' %s');

// Return of all selected CSS classes in specific group of a category as json within a data attribute
$this->styleManager->prepare('myCategoryIdentifier', ['alias1'])->format("data-slider='%s'", 'json');
```
In Twig:
```twig
{# Return of all selected CSS classes of a category within a class attribute #}
{{ styleManager(data).prepare('myCategoryIdentifier').format('class="%s"') }}

{# Additional classes are often appended to an existing class attribute. In this case, unnecessary if-else statements can be avoided by appending a space character if a value exists. #}
{{ styleManager(data).prepare('myCategoryIdentifier').format(' %s') }}

{# Return of all selected CSS classes in specific group of a category as json within a data attribute #}
{{ styleManager(data).prepare('myCategoryIdentifier', ['alias1']).format("data-slider='%s'", 'json') }}
```



## Format methods
### 🔸 `json`
Returns a JSON object using the alias and value (e.g. `{"alias1":"my-class-1","alias2":"my-class-2"}`)

### Create your own methods
To add your own methods, you can use the `styleManagerFormatMethod` hook:

```php
function customFormatMethod(string $format, string $method, Styles $context): string
{
    // Custom stuff
}
```
