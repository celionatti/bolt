<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= assets_path('img/favicon.png') ?>" />
    <link rel="apple-touch-icon" href="<?= assets_path('img/favicon.png') ?>" />
    <title>Default Page Title</title>
    <?php $this->content('header') ?>
</head>
<body>
    <?php $this->content('content'); ?>

    <?php $this->content('script') ?>
</body>
</html>