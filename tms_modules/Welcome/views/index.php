<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/'); ?>vendor/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css">
    <style>
        .bg-purple {
            background-color: #C300BD !important;
        }

        .bg-orange {
            background-color: orange !important;
        }

        .bg-yellow {
            background-color: yellow !important;
        }

        .bg-color-1 {
            background-color: #D9B959;
        }

        .bg-color-2 {
            background-color: #D09872;
        }

        .bg-color-3 {
            background-color: #D95959;
        }
    </style>
</head>

<body id="page-top">
    <?= $loading; ?>
    <div id="wrapper">
        <!-- Sidebar -->
        <?= $sidebar; ?>
        <!-- Sidebar -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- TopBar -->
                <?= $topbar; ?>
                <!-- TopBar -->
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <h1><?=$message;?></h1>
                </div>
                <!---Container Fluid-->
                <?= $modal_logout; ?>
            </div>
            <!-- Footer -->
            <?= $footer; ?>
            <!-- Footer -->
        </div>
    </div>
    <?= $foot; ?>

    <script>
        $(function () {
            
        });
    </script>
</body>

</html>