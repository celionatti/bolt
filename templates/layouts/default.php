<?php

use Bolt\Bolt\Helpers\FlashMessages\BootstrapFlashMessage;


?>

<!DOCTYPE html>
<html lang="en_US" data-bs-theme="auto">

<head>
    <script src="<?= get_script('color-modes.js') ?>"></script>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= get_image('img/favicon.png', "icon") ?>" />
    <link rel="apple-touch-icon" href="<?= get_image('img/favicon.png', "icon") ?>" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- AOS library -->
    <link type="text/css" rel="stylesheet" href="<?= get_package('aos/aos.css'); ?>">
    <!-- Bootstrap library -->
    <link type="text/css" rel="stylesheet" href="<?= get_bootstrap('css/bootstrap.min.css'); ?>">
    <link type="text/css" rel="stylesheet" href="<?= get_bootstrap('css/bootstrap-icons.css'); ?>">
    <!-- Box-Icons library -->
    <link type="text/css" rel="stylesheet" href="<?= get_package('boxicons/css/boxicons.min.css'); ?>">
    <!-- Light Box library -->
    <link type="text/css" rel="stylesheet" href="<?= get_package('glightbox/css/glightbox.min.css'); ?>">
    <title>Default Page Title</title>
    <?php $this->content('header') ?>
</head>

<body>
    <!-- Your Content goes in here. -->
    <?= BootstrapFlashMessage::alertSuccess(); ?>
    <?php $this->content('content'); ?>

    <script src="<?= get_package('jquery/jquery-3.6.3.min.js'); ?>"></script>
    <script src="<?= get_package('aos/aos.js'); ?>"></script>
    <script src="<?= get_bootstrap('js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?= get_package('glightbox/js/glightbox.min.js'); ?>"></script>
    <script src="<?= get_package('isotope-layout/isotope.pkgd.min.js'); ?>"></script>
    <script src="<?= get_package('swiper/swiper-bundle.min.js'); ?>"></script>
    <?php $this->content('script') ?>
</body>

</html>