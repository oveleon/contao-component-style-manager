# Contao Component Style Manager

Allows you to add your own groups of CSS classes provided in layouts, pages, articles, modules, forms, form elements and content elements to your customers.

This plugin is designed to simplify theme customizations without the need for customers to manually add classes or create new layouts.

### Overview
- Many possibilities of use (grid, designs, animations, content properties, ...)
- Clear representation in the backend
- Groups and Categories
- Available for
    - Layouts
    - Pages
    - Articles
    - Modules
    - Content-Elements
    - Forms
    - Form-Fields

> Feel free to use it as you need it.

---

### Examples
#### Admin:
##### List-View: Groups
![Admin View: List](https://www.oveleon.de/share/github-assets/contao-component-style-manager/list-view-120.png)
##### List-View: Categories
![Admin View: List](https://www.oveleon.de/share/github-assets/contao-component-style-manager/list-view-2-120.png)
##### Mask-View: Content Elements
![Admin View: Mask](https://www.oveleon.de/share/github-assets/contao-component-style-manager/content-elements-120.png)

#### Customer:
##### Article
![Customer View: Article](https://www.oveleon.de/share/github-assets/contao-component-style-manager/customer-120.png)

---

### Support Rocksolid Custom Elements
see: https://github.com/madeyourday/contao-rocksolid-custom-elements

Use the callback function `onloadCallback` in your custom element configuration and reference the following function:
```
 'onloadCallback' => array(
      array('Oveleon\ContaoComponentStyleManager\Support', 'extendRockSolidCustomElementsPalettes')
  )
```

---

### Latest Updates
#### 1.2.0
- Categories
- Better visualization in the backend
- Rocksolid CustomElement Support
- Further availability for
    - Layouts
    - Forms
    - Form-Fields
    - Modules

##### Fixes
- Multiple editing in the backend
- Additional security - classes remain after deleting groups or their values
- Alias has been removed from the palette and is created dynamically
- Sort by category-title
