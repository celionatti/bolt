<?php

/**
 * Framework Title: Bolt Framework
 * Creator: Celio natti
 * version: 1.0.0
 * Year: 2023
 * 
 * 
 * This view page start name{style,script,content} 
 * can be edited, base on what they are called in the layout view
 */

?>

<!-- For Adding CSS Styles -->
<?php $this->start('header') ?>

<?php $this->end() ?>

<!-- The Main content is Render here. -->
<?php $this->start('content') ?>
<?= partials("togglebtn") ?>

<!-- Content start here -->
<?= partials("navbar") ?>

<div>
    <div class="bg-body-tertiaryp-5 rounded">
        <div class="col-sm-8 mx-auto">
            <h1>Bolt Framework</h1>
            <p>This example is a quick exercise to illustrate how the navbar and its contents work. Some navbars extend the width of the viewport, others are confined within a <code>.container</code>. For positioning of navbars, checkout the <a href="../examples/navbar-static/">top</a> and <a href="../examples/navbar-fixed/">fixed top</a> examples.</p>
            <p>At the smallest breakpoint, the collapse plugin is used to hide the links and show a menu button to toggle the collapsed content.</p>
            <p>
                <a class="btn btn-primary" href="../components/navbar/" role="button">View navbar docs &raquo;</a>
            </p>
        </div>
    </div>
</div>
<?php $this->end() ?>

<!-- For Including JS function -->
<?php $this->start('script') ?>

<?php $this->end() ?>