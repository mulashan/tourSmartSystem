<?php

return [

    // Simple lookup lists — all stored in tbl_lookups, differentiated by 'type'.
    'lookups' => [
        'titles' => [
            'label' => 'Titles',
            'type' => 'title',
            'singular' => 'Title',
        ],
        'ranks' => [
            'label' => 'Rank',
            'type' => 'rank',
            'singular' => 'Rank',
        ],
        'measuring-units' => [
            'label' => 'Measuring Units',
            'type' => 'measuring_unit',
            'singular' => 'Measuring Unit',
        ],
        'item-categories' => [
            'label' => 'Item Categories',
            'type' => 'item_category',
            'singular' => 'Item Category',
        ],
    ],

    // Categories with real relationships — each needs its own table/controller.
    // Leave 'route' null until built; sidebar will show it disabled with "Coming soon".
    'custom' => [
        'branch-departments' => ['label' => 'Branch Departments', 'route' => true],
        'subdepartments'     => ['label' => 'Subdepartments', 'route' => true],
        'suppliers'          => ['label' => 'Suppliers', 'route' => true],
        'session-timeout' => ['label' => 'Session Timeout', 'route' => true],
    ],

];
