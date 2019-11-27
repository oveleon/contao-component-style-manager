# Contao Component Style Manager

Allows you to add your own groups of CSS classes provided in pages, articles, and content elements to your customers.

This plugin is designed to simplify theme customizations without the need for customers to manually add classes or create new layouts.

### Latest Updates
#### 1.1.2
- Categories
- Better visualization in the backend
- Rocksolid CustomElement Support

### Examples
#### Admin:
##### List-View
![Admin View: List](https://www.oveleon.de/share/github-assets/contao-component-style-manager/list-view.png)
##### Mask-View: Content Elements
![Admin View: Mask](https://www.oveleon.de/share/github-assets/contao-component-style-manager/content-elements.png)
##### Mask-View: Article
![Admin View: Mask](https://www.oveleon.de/share/github-assets/contao-component-style-manager/articles.png)

#### Customer:
##### Article
![Customer View: Article](https://www.oveleon.de/share/github-assets/contao-component-style-manager/customer.png)

#### Support Rocksolid Custom Elements
see: https://github.com/madeyourday/contao-rocksolid-custom-elements

Use the callback function `onloadCallback` in your custom element configuration and reference the following function:
```
 'onloadCallback' => array(
      array('Oveleon\ContaoComponentStyleManager\Support', 'extendRockSolidCustomElementsPalettes')
  )
```
