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
<?php $this->start('style') ?>
<style>
    /* Reset some default styles */
    body,
    h1,
    p {
        margin: 0;
        padding: 0;
    }

    /* Style for the jumbotron container */
    .jumbotron {
        /* background-color: #007bff; */
        background: linear-gradient(to bottom right, #3498db, #e74c3c);
        color: #fff;
        text-align: center;
        padding: 3rem 0;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    /* Style for the framework title */
    .framework-title {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 1rem;
        color: #dc3545;
        /* Reddish color for the title */
    }

    /* Style for the heading */
    .jumbotron h1 {
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    /* Style for the paragraph */
    .jumbotron p {
        font-size: 1.25rem;
        margin-bottom: 2rem;
    }

    /* Style for the call-to-action button */
    .btn-primary {
        background-color: #dc3545;
        color: #fff;
        padding: 1rem 2rem;
        font-size: 1.25rem;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: background-color 0.3s;
        text-decoration: none;
    }

    .btn-primary:hover {
        background-color: #c82333;
    }
</style>
<?php $this->end() ?>

<!-- The Main content is Render here. -->
<?php $this->start('content') ?>
<div class="jumbotron">
    <div class="framework-title"><span style="font-weight: bold;">⚡</span> Bolt Framework</div>
    <h1>Welcome to Our Framework</h1>
    <p>Build amazing web applications with ease.</p>
    <a href="#" onclick="startedClicked()" class="btn-primary">Get Started</a>
</div>
<?php $this->end() ?>

<!-- For Including JS function -->
<?php $this->start('script') ?>
<script>
    function startedClicked() {
        alert("Get Started Button Clicked...");
    }
</script>
<?php $this->end() ?>