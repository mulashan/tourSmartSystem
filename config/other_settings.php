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
        'ownership-types' => [
            'label' => 'Car Ownership Type', 
            'type' => 'ownership_type', 
            'singular' => 'Ownership Type'
            ],
        'fleet-locations' => [
            'label' => 'Fleet Current Location / Station', 
            'type' => 'fleet_location', 
            'singular' => 'Location'
            ],
        'insurance-types' => [
            'label' => 'Insurance Type', 
            'type' => 'insurance_type', 
            'singular' => 'Insurance Type'
            ],
        'insurance-coverages' => [
            'label' => 'Insurance Coverage', 
            'type' => 'insurance_coverage', 
            'singular' => 'Coverage'
            ],
        'destinations' => [
            'label' => 'Fleet Destinations', 
            'type' => 'destination', 
            'singular' => 'Destination'
            ],
        'fuel-sources' => [
            'label' => 'Fuel Sources', 
            'type' => 'fuel_source', 
            'singular' => 'Fuel Source'
            ],
        'employee-professionals' => [
            'label' => 'Employee Professionals',
            'type' => 'employee-professional',
            'singular' => 'Employee Professionals'
        ],
        'insurance-companies' => [
            'label' => 'Insurance Companies', 
            'type' => 'insurance_company', 
            'singular' => 'Insurance Company'
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
