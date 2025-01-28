<?php

return [
    'max_size' => 10240, // 10MB in kilobytes
    'max_files' => 5,
    'allowed_types' => [
        'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'
    ],
    'pagination' => [
        'default_limit' => 10,
        'max_limit' => 50
    ],
    'comments' => [
        'max_length' => 1000
    ]
];
