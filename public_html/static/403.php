<!DOCTYPE html>
<html>

<head>
    <?php
    http_response_code(401);
    require_once realpath($_SERVER['DOCUMENT_ROOT'] . "/../scripts/discord/vendor/autoload.php");
    Sentry\init(['dsn' => 'https://07d6860f8b104199b95a496171ebaef5@o435810.ingest.sentry.io/5397134' ]);
    $title = "Access denied";
    include "../../elements/title.php";
    include "../../elements/metadata.php";
    ?>
</head>

<body>
<?php include "../../elements/navbar.php"; ?>
<div class="container-fluid mt-5 p-5 w-75 min-vh-100 bg-light">
    <ul class="nav flex-column">
        <?php require_once '../../scripts/utils.php'; //failmodal("You do not have permission to view the specified resource", 30, "false", "true", 500); echo '<script>window.document.title = "401 Unauthorized"</script>'?>
        401 | Unauthorized
    </ul>
</div>
<?php include "../../elements/footer.php"; ?>
</body>

</html>
