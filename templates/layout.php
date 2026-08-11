<!-- templates/layout.php -->
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NewsMood — новости с эмоциональной окраской</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<div class="container">
    <header>
        <h1><a href="/" style="color:inherit; text-decoration:none;">NewsMood</a></h1>
    </header>
    <main>
        <?php echo $content ?? ''; ?>
    </main>
</div>
<script src="/assets/script.js"></script>
</body>
</html>