<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Framework Homepage</title>
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

        .desc {
            margin: 0;
            width: 700px;
            border: 5px solid #ccc;
            padding: 8px;
            border-radius: 1rem;
            font-size: 1.3rem;
        }
    </style>
</head>

<body>
    <div class="jumbotron">
        <div class="framework-title">
            <span style="font-weight: bold;">⚡</span>
            PhpStrike Framework
        </div>
        <p class="desc">Bolt Framework is a modern, open-source web application framework designed to empower developers to build robust and scalable web applications with ease. It offers a comprehensive suite of tools and features, making it a versatile choice for both beginners and experienced developers.</p>
        <a href="https://github.com/celionatti/PHPStrike#readme" target="_blank" class="btn-primary">Read Docs</a>
    </div>

    <script>
        function startedClicked() {
            alert("Get Started Button Clicked...");
        }
    </script>
</body>

</html>