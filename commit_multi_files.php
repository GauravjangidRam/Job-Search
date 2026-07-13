<?php
$files = [
    'start.sh',
    'Dockerfile',
    'fix_crlf.php',
    'resources/views/components/home/career-insights.blade.php',
    'resources/views/components/home/featured-jobs.blade.php'
];

for ($i = 0; $i < 10; $i++) {
    $file = $files[$i % count($files)];
    
    // Add space
    $content = file_get_contents($file);
    file_put_contents($file, $content . ' ');
    system('git add "' . $file . '" && git commit -m "Update ' . basename($file) . ' (add space)"');

    // Remove space
    $content = file_get_contents($file);
    file_put_contents($file, substr($content, 0, -1));
    system('git add "' . $file . '" && git commit -m "Update ' . basename($file) . ' (remove space)"');
} 
