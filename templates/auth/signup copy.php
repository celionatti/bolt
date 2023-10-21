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

use Bolt\Bolt\Forms\BootstrapForm;

?>

<!-- For Adding CSS Styles -->
<?php $this->start('header') ?>

<?php $this->end() ?>

<!-- The Main content is Render here. -->
<?php $this->start('content') ?>

<svg xmlns="http://www.w3.org/2000/svg" class="d-none">
    <symbol id="arrow-right-circle" viewBox="0 0 16 16">
        <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z" />
    </symbol>
    <symbol id="bolt" viewBox="0 0 118 94">
        <title>Bolt</title>
        <path fill-rule="evenodd" clip-rule="evenodd" d="M24.509 0c-6.733 0-11.715 5.893-11.492 12.284.214 6.14-.064 14.092-2.066 20.577C8.943 39.365 5.547 43.485 0 44.014v5.972c5.547.529 8.943 4.649 10.951 11.153 2.002 6.485 2.28 14.437 2.066 20.577C12.794 88.106 17.776 94 24.51 94H93.5c6.733 0 11.714-5.893 11.491-12.284-.214-6.14.064-14.092 2.066-20.577 2.009-6.504 5.396-10.624 10.943-11.153v-5.972c-5.547-.529-8.934-4.649-10.943-11.153-2.002-6.484-2.28-14.437-2.066-20.577C105.214 5.894 100.233 0 93.5 0H24.508zM80 57.863C80 66.663 73.436 72 62.543 72H44a2 2 0 01-2-2V24a2 2 0 012-2h18.437c9.083 0 15.044 4.92 15.044 12.474 0 5.302-4.01 10.049-9.119 10.88v.277C75.317 46.394 80 51.21 80 57.863zM60.521 28.34H49.948v14.934h8.905c6.884 0 10.68-2.772 10.68-7.727 0-4.643-3.264-7.207-9.012-7.207zM49.948 49.2v16.458H60.91c7.167 0 10.964-2.876 10.964-8.281 0-5.406-3.903-8.178-11.425-8.178H49.948z"></path>
    </symbol>
</svg>

<div class="col-lg-8 mx-auto p-4 py-md-5">
    <header class="d-flex align-items-center pb-3 mb-5 border-bottom">
        <a href="/" class="d-flex align-items-center text-body-emphasis text-decoration-none">
            <svg class="bi me-2" width="40" height="32">
                <use xlink:href="#bolt" />
            </svg>
            <span class="fs-4">Bolt Framework</span>
        </a>
    </header>

    <main>
        <h1 class="text-body-emphasis">Bolt Account Registration</h1>
        <p class="fs-5 col-md-8">Bolt Framework is a modern, open-source web application framework designed to empower developers to build robust and scalable web applications with ease. It offers a comprehensive suite of tools and features, making it a versatile choice for both beginners and experienced developers.</p>

        <p>User UUID: <?= $uuid ?></p>

        <hr class="col-3 col-md-2 mb-3">

        <?= BootstrapForm::openForm("") ?>
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <?= BootstrapForm::inputField("Username", "username", old_value("username"), ['class' => 'form-control'], ['class' => 'mb-3'], $errors) ?>
            </div>
            <div class="col-md-6 col-sm-12">
                <?= BootstrapForm::inputField("Email", "email", old_value("email"), ['class' => 'form-control', 'type' => 'email'], ['class' => 'mb-3'], $errors) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <?= BootstrapForm::inputField("Name", "name", old_value("name"), ['class' => 'form-control'], ['class' => 'mb-3'], $errors) ?>
            </div>
            <div class="col-md-6 col-sm-12">
                <?= BootstrapForm::inputField("Phone", "phone", old_value("phone"), ['class' => 'form-control', 'type' => 'tel'], ['class' => 'mb-3'], $errors) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <?= BootstrapForm::inputField("Password", "password", old_value("password"), ['class' => 'form-control', 'type' => 'password'], ['class' => 'mb-3'], $errors) ?>
            </div>
            <div class="col-md-6 col-sm-12">
                <?= BootstrapForm::inputField("Confirm Password", "confirm_password", old_value("confirm_password"), ['class' => 'form-control', 'type' => 'password'], ['class' => 'mb-3'], $errors) ?>
            </div>
        </div>
        <?= BootstrapForm::submitButton("Signup", "btn btn-secondary btn-sm p-3 w-50") ?>

        <?= BootstrapForm::closeForm() ?>
    </main>
    <footer class="pt-5 my-5 text-body-secondary border-top">
        Created by the Celio Natti &middot; &copy; 2023
    </footer>
</div>
<?php $this->end() ?>

<!-- For Including JS function -->
<?php $this->start('script') ?>

<?php $this->end() ?>