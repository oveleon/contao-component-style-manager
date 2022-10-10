# Support your own or vendor DCA's 
If you have your own DCA that you want to make available for the StyleManager, you can do this in **three to four steps**.
As in Contao itself, the DCA must contain a field where the CSS classes can be stored. The following fields are already included:

- `cssID` (multiple field)
- `cssClass` (single field)
- `class` (single field)
- `attributes` (multiple field)

> Please note that the field size must be observed!

### 1. Extending the **CSS group fields** in `tl_style_manager` DCA
  
```php
// Extend the default palette
Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addField(array('extendMyDca'), 'publish_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_style_manager');

// Extend fields
$GLOBALS['TL_DCA']['tl_style_manager']['fields']['extendMyDca'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['extendMyDca'],
    'exclude'                 => true,
    'filter'                  => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'clr'),
    'sql'                     => "char(1) NOT NULL default ''"
);
```

### 2. Adding the styleManager **legend and field** to your DCA
  
```php
// Extend fields
$GLOBALS['TL_DCA']['tl_mydca']['fields']['styleManager'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_mydca']['styleManager'],
    'exclude'                 => true,
    'inputType'               => 'stylemanager',
    'eval'                    => array('tl_class'=>'clr stylemanager'),
    'sql'                     => "blob NULL"
);

// Extend the palette (Since version 2.4 this callback method can be used, before that the field "styleManager" must be added via the palette manipulator.)
$GLOBALS['TL_DCA']['tl_mydca']['config']['onload_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'addPalette');

// Adding callback methods for the CSS-Class field (cssID, cssClass, class or attributes)
$GLOBALS['TL_DCA']['tl_mydca']['fields']['attributes']['load_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'onLoad');
$GLOBALS['TL_DCA']['tl_mydca']['fields']['attributes']['save_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'onSave');
```

### 3. Provide the StyleManager the new DCA

To get the selected CSS groups for the new DCA and to provide them in the backend, it is necessary to provide the StyleManager with the new DCA. In order to make this possible the **styleManagerFindByTable**-Hook is prepared.

```php
// HOOK
$GLOBALS['TL_HOOKS']['styleManagerFindByTable'][] = array('\\Namespace\\Class', 'onFindByTable');
```

```php
use Oveleon\ContaoComponentStyleManager\StyleManagerModel;

/**
 * Find css groups using their table
 *
 * @param string $strTable
 * @param array $arrOptions
 *
 * @return \Model\Collection|StyleManagerModel[]|StyleManagerModel|null A collection of models or null if there are no css groups
 */
public function onFindByTable($strTable, $arrOptions)
{
    if($strTable === 'tl_mydca')
    {
        return StyleManagerModel::findBy(array('extendMyDca=1'), null, $arrOptions);
    }

    return null;
}
```

### 4. Provide the StyleManager your new groups

To load the new CSS groups within your elements, it is necessary to provide the StyleManager the new groups. In order to make this possible the **styleManagerIsVisibleGroup**-Hook is prepared.


```php
// HOOK
$GLOBALS['TL_HOOKS']['styleManagerIsVisibleGroup'][] = array('\\Namespace\\Class', 'isVisibleGroup');
```

```php
use Oveleon\ContaoComponentStyleManager\StyleManagerModel;

/**
 * Check whether an element is visible in style manager widget
 *
 * @param $objGroup
 * @param $strTable
 *
 * @return bool
 */
public function isVisibleGroup($objGroup, $strTable)
{
        if(
            'tl_mydca1' === $strTable && !!$objGroup->extendMyDca1
        ){ return true; }

        return false;
}
```

### 5. **Skip fields** that should not be displayed in the Backend Select-Widget

📌 _This step is only necessary for tables with different types like tl_content, tl_module or tl_form_fields_

If the DCA provides several types, which can be selected individually under the CSS groups, a further check has to take place to display them only for certain types.

```php
// HOOK
$GLOBALS['TL_HOOKS']['styleManagerSkipField'][] = array('\\Namespace\\Class', 'onSkipField');
```

```php
/**
 * StyleManager Support
 *
 * If the field is not selected in the CSS group, it is skipped
 *
 * @param $objStyleGroups
 * @param $objWidget
 *
 * @return bool Skip field
 */
public function onSkipField($objStyleGroups, $objWidget)
{
    if(!!$objStyleGroups->extendMyDca && $objWidget->strTable === 'tl_mydca')
    {
        $arrDcaTypes = \StringUtil::deserialize($objStyleGroups->dcaTypes);

        if($arrDcaTypes !== null && !in_array($objWidget->activeRecord->type, $arrDcaTypes))
        {
            return true;
        }
    }

    return false;
}
```
