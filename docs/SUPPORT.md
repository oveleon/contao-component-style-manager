## Documentation

- [Configuration](CONFIGURATION.md)
- [Use template variables](TEMPLATE_VARIABLES.md)
- __[Support own extensions](SUPPORT.md)__
- [Bundle-Configurations](BUNDLE_CONFIG.md)

---

# Support own extensions

To use the StyleManager in your own DCA's as well, four to five steps are required.

## First things first

As in Contao itself, the DCA must contain a field where the CSS classes can be stored. One of the following fields
should exist:

| Field        | Size           |
|--------------|----------------|
| `cssID`      | multiple field |
| `cssClass`   | single field   |
| `class`      | single field   |
| `attributes` | multiple field |

> Please note that the field size must be observed!

## Step 1: Expand your DCA

Adding the StyleManager widget in your own DCA. As an example we use the DCA name `tl_mydca` and the Field `attribute` (
storage for CSS-Classes).

```php
// contao/config.php

use Oveleon\ContaoComponentStyleManager\Util\StyleManager;

// Extend the StyleManager field
$GLOBALS['TL_DCA']['tl_mydca']['fields']['styleManager'] = [
    'inputType' => 'stylemanager',
    'eval'      => ['tl_class'=>'clr stylemanager'],
    'sql'       => "blob NULL"
];
```

## Step 2: Add the onload, load and save callbacks

```php
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Oveleon\ContaoComponentStyleManager\EventListener\DataContainer\StyleManagerWidgetListener;

class StyleManagerCallback
{
    // Adding callback methods for the CSS-class field (cssID, cssClass, class or attributes)
    #[AsCallback(table: 'tl_mydca', target: 'fields.class.load')]
    public function onLoad($value, DataContainer $dc): mixed
    {
        return StyleManagerWidgetListener::clearClasses($value, $dc);
    }

    // Adding callback methods for the CSS-class field (cssID, cssClass, class or attributes)
    #[AsCallback(table: 'tl_mydca', target: 'fields.class.save')]
    public function onSave($value, DataContainer $dc): mixed
    {
        return StyleManagerWidgetListener::updateClasses($value, $dc);
    }

    // Extend the palette (StyleManager provides a helper callback to automatically include all palettes in the DCA, Contao's palette manipulator can also be used)
    #[AsCallback(table: 'tl_mydca', target: 'config.onload')]
    public function addPalette(DataContainer $dc): void
    {
        StyleManagerWidgetListener::applyToPalette($dc);
    }
}

```

## Step 3: Expand the StyleManager DCA

To be able to select the new DCA within the StyleManager configuration (CSS Groups), we need to make it known in the
next step. To use the same naming scheme as in StyleManager, we name our new field "extend`table-name`" (`extendMyDca`).

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

## Step 4: Make your DCA known to the StyleManager widget

To get the selected CSS groups for the new DCA and to provide them in the StyleManager widget, it is necessary to
provide the StyleManager with the new DCA. In order to make this possible the `styleManagerFindByTable` hook is
prepared.

```php
use Oveleon\ContaoComponentStyleManager\Event\StyleManagerFindByTableEvent;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
public function onFindByTable(StyleManagerFindByTableEvent $event): void
{
    if ('tl_mydca' === $event->getTable())
    {
        $event->setCollection(StyleManagerModel::findBy(['extendMyDca=1'], null, $event->getOptions()));
    }
}
```

## Step 5: Provide the StyleManager your new groups

To check if the CSS groups are allowed for the current component, we need to include a check function via the hook
`styleManagerIsVisibleGroup`.

```php
use Oveleon\ContaoComponentStyleManager\Event\StyleManagerVisibleGroupEvent;
use Oveleon\ContaoComponentStyleManager\StyleManagerModel;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
public function isVisibleGroup(StyleManagerVisibleGroupEvent $event): void
{
    if ('tl_mydca' === $event->getTable() && !!$event->getGroup()->extendMyDca)
    {
        $event->setVisible();
    }
}
```

## Step 6: **Skip fields** that should not be displayed

📌 _This step is only necessary for tables with different types like tl_content, tl_module or tl_form_fields_

If the DCA provides several types, which can be selected individually under the CSS groups, a further check has to take
place to display them only for certain types.

```php
use Contao\StringUtil;
use Contao\Widget;
use Oveleon\ContaoComponentStyleManager\Event\StyleManagerSkipFieldEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
public function onSkipField(StyleManagerVisibleGroupEvent $event): void
{
    $group = $event->getGroup();
    $widget = $event->getWidget();

    if (!!$group->extendMyDca && 'tl_mydca' === $widget->strTable)
    {
        $arrDcaTypes = StringUtil::deserialize($group->dcaTypes);

        if ($arrDcaTypes !== null && !in_array($widget->activeRecord->type, $arrDcaTypes))
        {
            $event->skip();
        }
    }
}
```

# Boilerplate

```php
<?php

namespace App/EventListener;

use Oveleon\ContaoComponentStyleManager\Event\StyleManagerFindByTableEvent;
use Oveleon\ContaoComponentStyleManager\Event\StyleManagerSkipFieldEvent;
use Oveleon\ContaoComponentStyleManager\Event\StyleManagerVisibleGroupEvent;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(StyleManagerFindByTableEvent::class, 'onFindByTable')]
#[AsEventListener(StyleManagerSkipFieldEvent::class, 'onSkipField')]
#[AsEventListener(StyleManagerVisibleGroupEvent::class, 'isVisibleGroup')]
class StyleManagerListener
{
    /**
     * Name of the DCA to be supported
     */
    const string DCA_NAME = 'tl_mydca';

    /**
     * Field name, which was added in tl_style_manager
     */
    const string SM_FIELD_NAME = 'extendMyDca';

    public function onFindByTable(StyleManagerFindByTableEvent $event): void
    {
        if (self::DCA_NAME === $event->getTable())
        {
            $event->setCollection(StyleManagerModel::findBy([self::SM_FIELD_NAME . '=1'], null, $event->getOptions()));
        }
    }


    public function isVisibleGroup(StyleManagerVisibleGroupEvent $event): void
    {
        if (self::DCA_NAME === $event->getTable() && !!$event->getGroup()->{self::SM_FIELD_NAME})
        {
            $event->setVisible();
        }
    }

    public function onSkipField(StyleManagerVisibleGroupEvent $event): void
    {
        $group = $event->getGroup();
        $widget = $event->getWidget();

        if (!!$group->{self::SM_FIELD_NAME} && self::DCA_NAME === $widget->strTable)
        {
            $arrDcaTypes = StringUtil::deserialize($group->dcaTypes);

            if ($arrDcaTypes !== null && !in_array($widget->activeRecord->type, $arrDcaTypes))
            {
                $event->skip();
            }
        }
    }
}
```
