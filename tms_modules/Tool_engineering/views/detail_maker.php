<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/'); ?>vendor/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css">
    <style>
        /* konsisten dengan style detail lainnya */
        .header-flex {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .card-header-title {
            font-size: 1.1rem;
            font-weight: 500;
        }

        html,
        body,
        #content-wrapper {
            color: #000;
        }

        .table,
        .card,
        label,
        .form-text,
        .dataTables_wrapper {
            color: #000;
        }
    </style>
</head>

<body id="page-top">
    <?= isset($loading) ? $loading : ''; ?>
    <div id="wrapper">
        <?= isset($sidebar) ? $sidebar : ''; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?= isset($topbar) ? $topbar : ''; ?>

                <div class="container-fluid" id="container-wrapper">
                    <div class="card mb-4">
                        <div class="card-header py-3 d-flex align-items-center justify-content-between">
                            <div>
                                <h4 class="m-0 font-weight-bold text-primary">Maker Details</h4>
                                <!-- <div class="small text-muted">ID: <?= htmlspecialchars($maker['MAKER_ID']); ?></div> -->
                            </div>
                            <div>
                                <a href="<?= base_url('tool_engineering/maker'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                    <i class="fa fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Maker:</strong> <?= htmlspecialchars($maker['MAKER_NAME']); ?></p>
                                    <p><strong>Maker Code:</strong> <?= htmlspecialchars($maker['MAKER_CODE']); ?></p>
                                    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($maker['MAKER_DESC'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($maker['MAKER_ADDRESS'])); ?></p>
                                    <p><strong>City / State / Zip Code:</strong>
                                        <?= htmlspecialchars($maker['MAKER_CITY']); ?>
                                        <?= (!empty($maker['MAKER_STATE']) ? ' / ' . htmlspecialchars($maker['MAKER_STATE']) : ''); ?>
                                        <?= (!empty($maker['MAKER_ZIPCODE']) ? ' / ' . htmlspecialchars($maker['MAKER_ZIPCODE']) : ''); ?>
                                    </p>
                                    <p><strong>Country:</strong> <?= htmlspecialchars($maker['MAKER_COUNTRY']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- jika kamu mau menambahkan card tambahan (history / linked items), taruh di sini -->

                </div>

                <?= isset($modal_logout) ? $modal_logout : ''; ?>
            </div>

            <?= isset($footer) ? $footer : ''; ?>
        </div>
    </div>

    <?= isset($foot) ? $foot : ''; ?>
    <script src="<?= base_url('assets/'); ?>vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url('assets/'); ?>vendor/datatables/dataTables.bootstrap4.min.js"></script>
</body>

</html>