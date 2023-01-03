<?php
$styles = [
    '/public/vendor/element-plus/css/index.css',
    '/public/assets/css/bootstrap.css',
];

foreach ($styles as $key => $value) {
    echo '<link rel="stylesheet" href="' . $value . '">';
}
