## Documentation
- [Configuration](CONFIGURATION.md)
- __[Use template variables](TEMPLATE_VARIABLES.md)__
- [Support own extensions](SUPPORT.md)
- [Import / Export](IMPORT_EXPORT.md)
- [Bundle-Configurations](BUNDLE_CONFIG.md)
- [Migration](docs/MIGRATE.md)

---

### As Twig is not yet fully supported (Contao ^5.1), this feature will only work with Legacy-Templates)
https://docs.contao.org/dev/framework/templates/legacy/

> To use legacy-templates (html5) in contao ^5.1, you can force this by overwriting config.php in "/contao/config/config.php".
```php
<?php

$GLOBALS['TL_CTE']['texts']['code']      = \Contao\ContentCode::class;
$GLOBALS['TL_CTE']['texts']['headline']  = \Contao\ContentHeadline::class;
$GLOBALS['TL_CTE']['texts']['html']      = \Contao\ContentHtml::class;
$GLOBALS['TL_CTE']['texts']['list']      = \Contao\ContentList::class;
$GLOBALS['TL_CTE']['texts']['text']      = \Contao\ContentText::class;
$GLOBALS['TL_CTE']['texts']['table']     = \Contao\ContentTable::class;

$GLOBALS['TL_CTE']['links']['hyperlink'] = \Contao\ContentHyperlink::class;
$GLOBALS['TL_CTE']['links']['toplink']   = \Contao\ContentToplink::class;

$GLOBALS['TL_CTE']['media']['image']     = \Contao\ContentImage::class;
$GLOBALS['TL_CTE']['media']['gallery']   = \Contao\ContentGallery::class;
$GLOBALS['TL_CTE']['media']['youtube']   = \Contao\ContentYouTube::class;
$GLOBALS['TL_CTE']['media']['vimeo']     = \Contao\ContentVimeo::class;
```

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

__Example:__
```php
// Return of all selected CSS classes of a category
$this->styleManager->get('myCategoryIdentifier');

// Return of all selected CSS classes in specific groups of a category
$this->styleManager->get('myCategoryIdentifier', ['alias1', 'alias2']);
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

__Example:__
```php
// Return of all selected CSS classes of a category within a class attribute
$this->styleManager->prepare('myCategoryIdentifier')->format('class="%s"');

// Additional classes are often appended to an existing class attribute. In this case, unnecessary if-else statements can be avoided by appending a space character if a value exists.
$this->styleManager->prepare('myCategoryIdentifier')->format(' %s');

// Return of all selected CSS classes in specific group of a category as json within a data attribute
$this->styleManager->prepare('myCategoryIdentifier', ['alias1'])->format("data-slider='%s'", 'json');
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
