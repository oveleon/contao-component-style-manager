## Documentation
- [Configuration](CONFIGURATION.md)
- [Use template variables](TEMPLATE_VARIABLES.md)
- __[Support own extensions](SUPPORT.md)__
- [Import / Export](IMPORT_EXPORT.md)
- [Bundle-Configurations](BUNDLE_CONFIG.md)
- [Migration](docs/MIGRATE.md)

---

# Support own extensions
To use the StyleManager in your own DCA's as well, four to five steps are required.

## First things first
As in Contao itself, the DCA must contain a field where the CSS classes can be stored. One of the following fields should exist:

| Field        | Size           |
|--------------|----------------|
| `cssID`      | multiple field |
| `cssClass`   | single field   |
| `class`      | single field   |
| `attributes` | multiple field |

> Please note that the field size must be observed!

## Step 1: Expand your DCA
Adding the StyleManager widget in your own DCA. As an example we use the DCA name `tl_mydca` and the Field `attribute` (storage for CSS-Classes).

```php
// contao/config.php

use Oveleon\ContaoComponentStyleManager\StyleManager\StyleManager;

// Extend the StyleManager field
$GLOBALS['TL_DCA']['tl_mydca']['fields']['styleManager'] = [
    'inputType' => 'stylemanager',
    'eval'      => ['tl_class'=>'clr stylemanager'],
    'sql'       => "blob NULL"
];

// Extend the palette (StyleManager provides a helper callback to automatically include all palettes in the DCA, Contao's palette manipulator can also be used)
$GLOBALS['TL_DCA']['tl_mydca']['config']['onload_callback'][] = [StyleManager::class, 'addPalette'];

// Adding callback methods for the CSS-class field (cssID, cssClass, class or attributes)
$GLOBALS['TL_DCA']['tl_mydca']['fields']['attributes']['load_callback'][] = [StyleManager::class, 'onLoad'];
$GLOBALS['TL_DCA']['tl_mydca']['fields']['attributes']['save_callback'][] = [StyleManager::class, 'onSave'];
```

## Step 2: Expand the StyleManager DCA
To be able to select the new DCA within the StyleManager configuration (CSS Groups), we need to make it known in the next step. To use the same naming scheme as in StyleManager, we name our new field "extend`table-name`" (`extendMyDca`).

```php
use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Extend fields
$GLOBALS['TL_DCA']['tl_style_manager']['fields']['extendMyDca'] = [
    'inputType' => 'checkbox',
    'eval'      => ['tl_class'=>'clr'],
    'sql'       => "char(1) NOT NULL default ''"
];

// Extend the default palette
PaletteManipulator::create()
    ->addField(['extendMyDca'], 'publish_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_style_manager')
;
```

## Step 3: Make your DCA known to the StyleManager widget

To get the selected CSS groups for the new DCA and to provide them in the StyleManager widget, it is necessary to provide the StyleManager with the new DCA. In order to make this possible the `styleManagerFindByTable` hook is prepared.

```php
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;

#[AsHook('styleManagerFindByTable')]
public function onFindByTable(string $table, array $options = [])
{
    if ('tl_mydca' === $table)
    {
        return StyleManagerModel::findBy(['extendMyDca=1'], null, $options);
    }

    return null;
}
```

## Step 4: Provide the StyleManager your new groups

To check if the CSS groups are allowed for the current component, we need to include a check function via the hook `styleManagerIsVisibleGroup`.

```php
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Oveleon\ContaoComponentStyleManager\StyleManagerModel;

#[AsHook('styleManagerIsVisibleGroup')]
public function isVisibleGroup(StyleManagerModel $group, string $table): bool
{
    if ('tl_mydca' === $table && !!$group->extendMyDca)
    {
        return true;
    }

    return false;
}
```

## Step 5: **Skip fields** that should not be displayed

📌 _This step is only necessary for tables with different types like tl_content, tl_module or tl_form_fields_

If the DCA provides several types, which can be selected individually under the CSS groups, a further check has to take place to display them only for certain types.

```php
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\StringUtil;
use Contao\Widget;

#[AsHook('styleManagerSkipField')]
public function onSkipField(StyleManagerModel $styleGroups, Widget $widget)
{
    if(!!$styleGroups->extendMyDca && 'tl_mydca' === $widget->strTable)
    {
        $arrDcaTypes = StringUtil::deserialize($styleGroups->dcaTypes);

        if($arrDcaTypes !== null && !in_array($widget->activeRecord->type, $arrDcaTypes))
        {
            return true;
        }
    }

    return false;
}
```

# Boilerplate

```php
<?php

namespace App/EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\StringUtil;
use Contao\Widget;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;

class StyleManagerListener
{
    /**
     * Name of the DCA to be supported
     */
    const DCA_NAME = 'tl_mydca';

    /**
     * Field name, which was added in tl_style_manager
     */
    const SM_FIELD_NAME = 'extendMyDca';

    #[AsHook('styleManagerFindByTable')]
    public function onFindByTable(string $table, array $options = []): StyleManagerModel|Collection|array|null
    {
        if (self::DCA_NAME === $table)
        {
            return StyleManagerModel::findBy([self::SM_FIELD_NAME . '=1'], null, $options);
        }

        return null;
    }

    #[AsHook('styleManagerIsVisibleGroup')]
    public function isVisibleGroup(StyleManagerModel $group, string $table): bool
    {
        if (self::DCA_NAME === $table && !!$group->{self::SM_FIELD_NAME})
        {
            return true;
        }

        return false;
    }

    #[AsHook('styleManagerSkipField')]
    public function onSkipField(StyleManagerModel $styleGroups, Widget $widget): bool
    {
        if(!!$styleGroups->{self::SM_FIELD_NAME} && self::DCA_NAME === $widget->strTable)
        {
            $arrDcaTypes = StringUtil::deserialize($styleGroups->dcaTypes);

            if($arrDcaTypes !== null && !in_array($widget->activeRecord->type, $arrDcaTypes))
            {
                return true;
            }
        }

        return false;
    }
}
```
