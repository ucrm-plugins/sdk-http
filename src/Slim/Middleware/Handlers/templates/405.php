<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Method Not Allowed</title>
    <style><?php include("styles.css"); ?></style>
</head>
<body>
    <h1>Method Not Allowed</h1>
    <p>The current HTTP method/verb is not support by this endpoint.</p>

    <?php
    use UCRM\HTTP\Slim\Middleware\Authentication\Authenticators\Authenticator;

    /**
     * @var bool            $debug
     * @var string          $vRoute
     * @var array           $vQuery
     * @var Authenticator   $authenticator
     * @var array           $methods
     */
    if (isset($data))
        list(
            $debug,
            $vRoute,
            $vQuery,
            $authenticator,
            $methods
        ) = array_values($data);
    ?>

    <?php if ($debug) { ?>

        <h4>Virtual Route:</h4>
        <ul>
            <li><?php echo $vRoute; ?></li>
        </ul>

        <?php if ($vQuery) { ?>
            <h4>Virtual Query Parameters:</h4>
            <ul>
                <?php foreach ($vQuery as $key => $value) { ?>
                    <li><?php echo $key; ?> = <?php echo $value; ?></li>
                <?php } ?>
            </ul>
        <?php } ?>

        <?php if ($methods) { ?>
            <h4>Supported Methods:</h4>
            <ul>
                <li>
                    <?php foreach ($methods as $method) { ?>
                        <span class="badge"><?php echo $method; ?></span>
                    <?php } ?>
                </li>
            </ul>
        <?php } ?>

        <?php if ($authenticator) { ?>
            <h4>Using Authenticator:</h4>
            <ul>
                <li><?php echo $authenticator; ?></li>
            </ul>
        <?php } ?>

    <?php } ?>

    <a href="<?php echo $_SERVER["SCRIPT_NAME"]; ?>">Home</a>
</body>
</html>
