<?php

/**
 * Bolt Navbar
 * 
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

<nav class="navbar navbar-expand-lg bg-body-tertiary rounded" aria-label="Thirteenth navbar example">
    <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample11" aria-controls="navbarsExample11" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse d-lg-flex" id="navbarsExample11">
            <a class="navbar-brand col-lg-3 me-0" href="#">Bolt Framework</a>
            <ul class="navbar-nav col-lg-6 justify-content-lg-center">
                <li class="nav-item">
                    <?= bolt_link("#", "nav-link active", "Home", ['aria-current' => 'page']) ?>
                </li>
                <li class="nav-item">
                    <?= bolt_link("#", "nav-link disabled", "Docs", ['aria-disabled' => 'true']) ?>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">Dropdown</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Action</a></li>
                        <li><a class="dropdown-item" href="#">Another action</a></li>
                        <li><a class="dropdown-item" href="#">Something else here</a></li>
                    </ul>
                </li>
            </ul>
            <div class="d-lg-flex col-lg-3 justify-content-lg-end">
                <button class="btn btn-primary rounded-pill">AU</button>
            </div>
        </div>
    </div>
</nav>