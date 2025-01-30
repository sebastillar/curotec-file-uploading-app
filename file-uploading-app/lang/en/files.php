<?php

return [
    'messages' => [
        'uploaded' => 'File uploaded successfully',
        'multiple_uploaded' => 'Files uploaded successfully',
        'version_added' => 'New version added successfully',
        'comment_added' => 'Comment added successfully',
    ],
    'validation' => [
        'file_size' => 'The file size must not exceed :size MB.',
        'file_types' => 'The file must be of type: :types.',
        'max_files' => 'You can upload up to :count files at once.',
        'comment_length' => 'The comment must not exceed :length characters.',
    ],
    'errors' => [
        'version_not_found' => 'The requested version was not found.',
        'file_not_found' => 'The file was not found in storage.',
        'download_denied' => 'You do not have permission to download this file.',
    ],
];
