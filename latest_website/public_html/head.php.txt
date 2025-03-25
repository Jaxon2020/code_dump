<?php
// /public_html/includes/head.php

// Ensure session is started only once
if (session_id() === '') {
    session_start();
}

// Default title if none is provided
$title = isset($pageTitle) ? $pageTitle : 'FarmMarket';

// Array of page-specific CSS files (default to empty array)
$pageSpecificCss = isset($pageSpecificCss) ? $pageSpecificCss : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <!-- Global Styles -->
    <link rel="stylesheet" href="/css/reset.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/css/themes.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/css/navbar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/css/components.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/css/footer.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/css/responsive.css?v=<?php echo time(); ?>">
    <!-- Page-Specific Styles -->
    <?php foreach ($pageSpecificCss as $cssFile): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($cssFile); ?>?v=<?php echo time(); ?>">
    <?php endforeach; ?>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Amatic+SC:wght@700&family=Georgia&display=swap" rel="stylesheet">
</head>
<body data-theme="<?php echo htmlspecialchars($_SESSION['theme'] ?? 'original'); ?>">