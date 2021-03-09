# Manage Categories:
### Fields:
- `Title`: The title of the category, which is displayed above the defined style groups in the backend
- `Idenfifier`: A unique value for retrieving the classes in the template
- `Group-Idenfifier`: In this field you can specify an alias that combines categories with the same alias and displays them as tabs in the backend.
- `Sort-Index`: This field is used to determine the order of the categories in the backend

#### Example backend view of combined categories using `Group-Idenfifier`:
![Manage Categories: Image 3](https://www.oveleon.de/share/github-assets/contao-component-style-manager/2.0/combined-groups.png)

<br/>

# Manage CSS-Groups:
### Fields:
- `Alias`: Define an alias with which the group can be accessed. This is only required for passing on to the template.
- `Add search field`: Use of chosen for a search field within the select box
- `Use as template variable`: This field declares whether this group is set in the class attribute of the corresponding element or passed to the template.
- `CSS class`: To further customize the display of the backend fields, you can enter a selection of predefined CSS classes. (long, clr, separator)
> All other fields should be self-explanatory

#### Example css group:
![Manage Groups: Image 1](https://www.oveleon.de/share/github-assets/contao-component-style-manager/2.0/groups-edit.png)
