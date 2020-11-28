<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 9/12/18
 * Time: 2:55 AM
 */

namespace A2nt\TreeMultiselectFieldSortable;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use Exception;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\TreeMultiselectField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\Security\Group;
use SilverStripe\View\ViewableData;
use SilverStripe\View\Requirements;

class TreeMultiselectFieldSortable extends TreeMultiselectField
{
    private $sortField;
    private static $allowed_actions = [
        'sort',
    ];

    public function __construct(
        $sortField,
        $name,
        $title = null,
        $sourceObject = Group::class,
        $keyField = 'ID',
        $labelField = 'Title'
    ) {
        parent::__construct($name, $title, $sourceObject, $keyField, $labelField);

        $this->addExtraClass('sortable');
        $this->sortField = $sortField;
    }

    public function Field($properties = [])
    {
        Requirements::css('a2nt/silverstripe-treemultiselect-sortable-field:client/dist/css/app.css');
        Requirements::javascript('a2nt/silverstripe-treemultiselect-sortable-field:client/dist/js/app.js');

        return parent::Field($properties);
    }

    public function getSchemaDataDefaults()
    {
        $data = parent::getSchemaDataDefaults();

        $data['data'] = array_merge($data['data'], [
            'url_sort' => $this->Link('sort'),
            'sort_order' => implode(',', array_keys($this->getItems()->map()->toArray())),
        ]);

        return $data;
    }

    public function getItems()
    {
        $items = new ArrayList();

        // If the value has been set, use that
        if ($this->value != 'unchanged') {
            $sourceObject = $this->getSourceObject();
            if (is_array($sourceObject)) {
                $values = is_array($this->value) ? $this->value : preg_split('/ *, */', trim($this->value));

                foreach ($values as $value) {
                    $item = new stdClass;
                    $item->ID = $value;
                    $item->Title = $sourceObject[$value];
                    $items->push($item);
                }

                return $items;
            }

            // Otherwise, look data up from the linked relation
            if (is_string($this->value)) {
                $ids = explode(',', $this->value);
                foreach ($ids as $id) {
                    if (!is_numeric($id)) {
                        continue;
                    }
                    $item = DataObject::get_by_id($sourceObject, $id);
                    if ($item) {
                        $items->push($item);
                    }
                }
                return $items;
            }
        }

        if ($this->form) {
            $fieldName = $this->name;
            $record = $this->form->getRecord();
            if (is_object($record) && $record->hasMethod($fieldName)) {
                $items = $record->$fieldName()->sort($this->sortField.' ASC');
                return $items;
            }
        }

        return $items;
    }

    /**
     * Get the whole tree of a part of the tree via an AJAX request.
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     * @throws Exception
     */
    public function sort(HTTPRequest $request)
    {
        $name = $this->getName();
        $data = $request->postVar($name);
        $objName = $this->getSourceObject();
        $objName = substr($objName, strrpos($objName, '\\')+1);
        $table = $this->getItems()->getJoinTable();
        $field = $this->sortField;

        $i = 0;
        foreach ($data as $id) {
            $query = 'UPDATE '.$table.' SET '.$field.' = '.($i + 1)
                .' WHERE '.$table.'.'.$objName.'ID = '.Convert::raw2sql($data[$i]).';';
            DB::query($query);
            $i++;
        }

        return json_encode($data);
    }
}
