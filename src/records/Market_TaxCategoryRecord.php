<?php

namespace Craft;

/**
 * Class Market_TaxCategoryRecord
 *
 * @property int    $id
 * @property string $name
 * @property string $handle
 * @property string $description
 * @property bool   $default
 * @package Craft
 */
class Market_TaxCategoryRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'market_taxcategories';
    }

    public function defineIndexes()
    {
        return [
            ['columns' => ['handle'], 'unique' => true],
        ];
    }

    protected function defineAttributes()
    {
        return [
            'name'        => [AttributeType::String, 'required' => true],
            'handle'      => [AttributeType::String, 'required' => true],
            'description' => AttributeType::String,
            'default'     => [
                AttributeType::Bool,
                'default'  => 0,
                'required' => true
            ],
        ];
    }

}