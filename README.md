# silverstripe-treemultiselect-sortable-field
# SilverStripe Tree Multi-select sortable field

SilverStipe custom sort field on tree items

## Installation

```
composer require a2nt/silverstripe-treemultiselect-sortable-field
```

## Usage

- Install the module, run dev/build
- Add your custom field

```
 private static $many_many = [
    'FooterNavigation' => SiteTree::class,
];

private static $many_many_extraFields = [
    'FooterNavigation' => [
        'SortOrder' => 'Int',
    ],
];

TreeMultiselectFieldSortable::create(
    'SortOrder', // order field name
    'FooterNavigation', // relation name
    'Footer Navigation', // field title
    SiteTree::class // relation objects class name
)
```
