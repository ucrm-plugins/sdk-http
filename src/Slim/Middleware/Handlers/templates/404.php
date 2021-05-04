<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Not Found</title>
    <style><?php include("styles.css"); ?></style>
</head>
<body>
    <h1>Not Found</h1>
    <p>The page you are looking for could not be found.</p>

    <?php
    use UCRM\HTTP\Slim\Middleware\Authentication\Authenticators\Authenticator;
    use Slim\Interfaces\RouteInterface;

    /**
     * @var bool                $debug
     * @var string              $vRoute
     * @var array               $vQuery
     * @var Authenticator       $authenticator
     * @var RouteInterface[]    $routes
     * @var string              $message
     */
    if (isset($data))
        list(
            $debug,
            $vRoute,
            $vQuery,
            $authenticator,
            $routes,
            $message
        ) = array_values($data);
    ?>

    <?php if ($debug) { ?>

        <?php if ($message && $message !== "Not found.") { ?>
            <h4>Message:</h4>
            <ul>
                <li><?php echo $message; ?></li>
            </ul>
        <?php } ?>

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

        <?php if ($routes) { ?>
            <h4>Available Routes:</h4>
            <ul>
                <?php foreach ($routes as $route) { ?>
                <li>
                    <span><?php echo $route->getPattern(); ?></span>
                    <?php foreach ($route->getMethods() as $method) { ?>
                        <span class="badge"><?php echo $method; ?></span>
                    <?php } ?>
                </li>
                <?php } ?>
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
