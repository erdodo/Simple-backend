
<?php
$styles = [
    '/public/assets/js/vue.js',
    '/public/vendor/element-plus/js/index.js'
];

foreach ($styles as $key => $value) {
    echo '<script  href="' . $value . '"></script>';
}

?>
