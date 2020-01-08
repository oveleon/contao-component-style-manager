# Contao Component Style Manager
[![stable](https://img.shields.io/badge/stable-master-%23D6AF23?style=flat-square)](https://github.com/oveleon/contao-component-style-manager/tree/master)
[![unstable](https://img.shields.io/badge/unstable-develop-F38041?style=flat-square)](https://github.com/oveleon/contao-component-style-manager/tree/develop)
[![downloads](https://img.shields.io/packagist/dt/oveleon/contao-component-style-manager?color=%230A7BBC&style=flat-square)](https://packagist.org/packages/oveleon/contao-component-style-manager)
[![license](https://img.shields.io/github/license/oveleon/contao-component-style-manager?style=flat-square)](https://github.com/oveleon/contao-component-style-manager/blob/master/LICENSE)

Allows you to easily manage you own CSS classes as groups provided in layouts, pages, articles, modules, news, events, forms, form elements and content elements.

This plugin is designed to simplify theme customizations without the need of manually adding classes or creating new layouts.

## Overview
- Many possibilities of use (grid, animations, content properties, ...)
- Clear representation in the backend
- Groups and Categories
    - Combine and output as tabs [![new](https://img.shields.io/badge/-new-brightgreen?style=flat-square)](#manage-categories)
- Passing variables to the template [![new](https://img.shields.io/badge/-new-brightgreen?style=flat-square)](#passing-style-group-variables-to-a-template)
- Available for
    - Layouts
    - Pages
    - Articles
    - Modules
    - Content-Elements
    - Forms
    - Form-Fields
    - News [![new](https://img.shields.io/badge/-new-brightgreen?style=flat-square)](#contao-component-style-manager)
    - Events [![new](https://img.shields.io/badge/-new-brightgreen?style=flat-square)](#contao-component-style-manager)
- Third-Party plugin support
    - Rocksolid Custom Elements 

<br/>

> Feel free to use it as you need it


## Install
```
$ composer require oveleon/contao-component-style-manager
```

## Manage Categories:
#### Fields:
- `Title`: The title of the category, which is displayed above the defined style groups in the backend
- `Idenfifier`: A unique value for retrieving the classes in the template
- `Group-Idenfifier`: In this field you can specify an alias that combines categories with the same alias and displays them as tabs in the backend.
- `Sort-Index`: This field is used to determine the order of the categories in the backend

#### Examples:
![Manage Categories: Image 1](https://www.oveleon.de/share/github-assets/contao-component-style-manager/2.0/categorie-edit-1.png)

![Manage Categories: Image 2](https://www.oveleon.de/share/github-assets/contao-component-style-manager/2.0/categories.png)

##### Example view of the combined categories (Group-Idenfifier: "grid"):
![Manage Categories: Image 3](https://www.oveleon.de/share/github-assets/contao-component-style-manager/2.0/user-grid.png)

## Manage style groups:
#### Fields:
- `Add search field`: Use of chosen for a search field within the select box
- `Use as template variable`: This field declares whether this style group is set in the class of the corresponding element or passed to the template.

#### Examples:
![Manage Categories: Image 1](https://www.oveleon.de/share/github-assets/contao-component-style-manager/2.0/style-groups-edit.png)

![Manage Categories: Image 1](https://www.oveleon.de/share/github-assets/contao-component-style-manager/2.0/style-groups-list.png)

## Passing style group variables to a template:
If the checkbox "Use as template variable" is set, these are not automatically passed to the class of the corresponding element but are available in the template.

To access the variables, we can access the corresponding class collection via the StyleManager object.

#### Examples:
```php
// Return of all selected CSS classes of a category
$this->styleManager->get('myCategoryIdentifier');

// Return of all selected CSS classes in specific groups of a category
$this->styleManager->get('myCategoryIdentifier', ['group1', 'group2']);
```
##### Example of use
```html
<div 
    class="<?=$this->styleManager->get('myCategoryIdentifier', ['group1'])?>" 
    data-attr="<?=$this->styleManager->get('myCategoryIdentifier', ['group2'])?>">
</div>
```

![Passing Variables: Image 1](https://www.oveleon.de/share/github-assets/contao-component-style-manager/2.0/template-var-list.png)

## Support Rocksolid Custom Elements
see: [Rocksolid Custom Elements](https://github.com/madeyourday/contao-rocksolid-custom-elements)

Use the callback function `onloadCallback` in your custom element configuration and reference the following function:
```
 'onloadCallback' => array(
      array('Oveleon\ContaoComponentStyleManager\Support', 'extendRockSolidCustomElementsPalettes')
  )
```
