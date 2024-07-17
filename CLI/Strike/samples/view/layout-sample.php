<?php

use celionatti\Bolt\Helpers\FlashMessages\BootstrapFlashMessage;


?>

<!DOCTYPE html>
<html lang="en_us" data-bs-theme="auto">

<head>
    <script src="<?= asset('bootstrap/js/color-modes.js') ?>"></script>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= get_image('img/favicon.png', "icon") ?>" />
    <link rel="apple-touch-icon" href="<?= get_image('img/favicon.png', "icon") ?>" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Bootstrap library -->
    <link type="text/css" rel="stylesheet" href="<?= asset('bootstrap/css/bootstrap.min.css'); ?>">
    <link type="text/css" rel="stylesheet" href="<?= asset('css/bootstrap-icons.css'); ?>">

    <title>Bolt Framework | Home</title>
    <?php $this->content('header') ?>
</head>

<body>
    {{ BootstrapFlashMessage::alert() }}
    <!-- Your Content goes in here. -->
    <?php $this->content('content'); ?>

    <script src="<?= asset('jquery/jquery-3.6.3.min.js'); ?>"></script>
    <?php $this->content('script') ?>
</body>

</html>