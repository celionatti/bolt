<!DOCTYPE html>
<html>
<head>
    <title><?php echo e($title); ?></title>
</head>
<body>
    <h1><?php echo e($header); ?></h1>

    <p>Hello, world!</p>

    <ul>
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($item); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\bolt\templates\blade-views/welcome.blade.php ENDPATH**/ ?>