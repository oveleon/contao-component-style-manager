# Passing css group variables to a template:
If the checkbox "Use as template variable" is set, these are not automatically passed to the class of the corresponding element but are available in the template.
To access the variables, we can access the corresponding class collection via the `styleManager` object.

![Passing Variables: Image 1](https://www.oveleon.de/share/github-assets/contao-component-style-manager/2.0/template-vars-list.png)

### There are two ways to receive the values:
- **`get`**: Return selected CSS classes of a category or a specific group
    - Parameter:
        - `identifier: string`: Category identifier
        - `groups: null|array` (optional): Group aliases
- **`prepare`** + **`format`**: Different from the get method, you can specify your own output format and a predefined or custom method to validate the output
    - `prepare`-Parameter: 
        - `identifier: string`: Category identifier
        - `groups: null|array` (optional): Group aliases
    - `format`-Parameter:
        - `format: string`: The format parameter must contain a format string valid for `sprintf` (PHP: [sprintf](https://www.php.net/manual/de/function.sprintf.php))).
        - `method: string` (optional): Name of Method
        
#### `format`-Methods
- `json`: Returns a JSON object using the alias and value (e.g. `{"alias1":"my-class-1","alias2":"my-class-2"}`)

To set up a custom method for validating the values, the hook `styleManagerFormatMethod` can be registered.

### Examples:
#### Using `get`-Method
```php
// Return of all selected CSS classes of a category
$this->styleManager->get('myCategoryIdentifier');

// Return of all selected CSS classes in specific groups of a category
$this->styleManager->get('myCategoryIdentifier', ['alias1', 'alias2']);
```

#### Using `prepare` + `format`-Method
```php
// Return of all selected CSS classes of a category with class attribute
$this->styleManager->prepare('myCategoryIdentifier')->format('class="%s"');

// Often additional classes are appended to an existing class attribute. In this case, unnecessary if-else statements can be avoided.
$this->styleManager->prepare('myCategoryIdentifier')->format(' %s');

// Return of all selected CSS classes in specific groups of a category as json with data attribute
$this->styleManager->prepare('myCategoryIdentifier', ['alias1'])->format("data-slider='%s'", 'json');
```

#### Example of use
```
<div class="<?=$this->styleManager->get('myCategoryIdentifier')?>">...</div>
  or
<div <?=$this->styleManager->prepare('myCategoryIdentifier')->format("class="%s")?>>...</div>

<div class="my-class-1<?=$this->styleManager->prepare('myCategoryIdentifier')->format(' %s')?>">...</div>

<div data-slider="<?=$this->styleManager->prepare('myCategoryIdentifier', ['slider-alias'])->format("data-slider='%s'", 'json')?>">...</div>
```

