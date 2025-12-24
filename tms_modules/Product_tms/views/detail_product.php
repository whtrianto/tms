<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/'); ?>vendor/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css">
    <style>
        /* Header flex & judul agar konsisten */
        .header-flex {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .card-header-title {
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* === GLOBAL: jadikan semua teks hitam === */
        html,
        body,
        #content-wrapper {
            color: #000;
        }

        .table,
        .card,
        .modal,
        .dropdown-menu,
        .form-text,
        .dataTables_wrapper,
        label,
        .invalid-feedback,
        .valid-feedback {
            color: #000;
        }

        .form-control,
        .custom-select,
        input,
        textarea,
        select {
            color: #000 !important;
        }

        ::placeholder {
            color: #000 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered,
        .select2-results__option {
            color: #000 !important;
        }

        /* Minor spacing for details */
        .detail-label {
            width: 160px;
            display: inline-block;
            font-weight: 600;
        }

        .detail-value {
            display: inline-block;
        }

        .meta-small {
            font-size: .9rem;
            color: #666;
        }

        /* responsive small tweaks */
        @media (max-width: 576px) {
            .detail-label {
                display: block;
                width: 100%;
                margin-bottom: 4px;
            }

            .detail-value {
                display: block;
            }
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
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <div>
                                <h4 class="m-0 font-weight-bold text-primary">Product Details</h4>
                                <!-- <div class="meta-small">
                                    ID: <?= isset($product['PART_ID']) ? (int)$product['PART_ID'] : ''; ?>
                                </div> -->
                            </div>

                            <div class="header-flex">
                                <a href="<?= base_url('product_tms/product'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                    <i class="fa fa-arrow-left"></i> Back
                                </a>

                                <?php if (isset($product['PART_ID']) && $product['PART_ID'] !== ''): ?>
                                    <a href="<?= base_url('product_tms/product') . '/edit/' . (int)$product['PART_ID']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p>
                                        <span class="detail-label">Product Name</span>
                                        <span class="detail-value"><?= isset($product['PART_NAME']) ? htmlspecialchars($product['PART_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                    </p>

                                    <p>
                                        <span class="detail-label">Product Group</span>
                                        <span class="detail-value"><?= isset($product['PART_GROUP']) ? htmlspecialchars($product['PART_GROUP'], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                    </p>

                                    <p>
                                        <span class="detail-label">Description</span>
                                        <span class="detail-value"><?= isset($product['PART_DESC']) ? nl2br(htmlspecialchars($product['PART_DESC'], ENT_QUOTES, 'UTF-8')) : ''; ?></span>
                                    </p>

                                    <p>
                                        <span class="detail-label">Type</span>
                                        <span class="detail-value"><?= isset($product['PART_TYPE']) ? htmlspecialchars($product['PART_TYPE'], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                    </p>

                                </div>

                                <div class="col-md-6">
                                    <p>
                                        <span class="detail-label">Customer Code</span>
                                        <span class="detail-value"><?= isset($product['PART_CUS_CODE']) ? htmlspecialchars($product['PART_CUS_CODE'], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                    </p>

                                    <p>
                                        <span class="detail-label">Customer Name</span>
                                        <span class="detail-value"><?= isset($product['CUS_NAME']) ? htmlspecialchars($product['CUS_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                    </p>

                                    <p>
                                        <span class="detail-label">UOM</span>
                                        <span class="detail-value"><?= isset($product['UOM_NAME']) ? htmlspecialchars($product['UOM_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                    </p>


                                    <p>
                                        <span class="detail-label">Drawing No</span>
                                        <span class="detail-value"><?= isset($product['PART_DRW_NO']) ? htmlspecialchars($product['PART_DRW_NO'], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                                    </p>
                                </div>
                            </div>

                            <!-- optional: children list jika ada -->
                            <?php if (isset($children) && is_array($children) && count($children) > 0): ?>
                                <hr>
                                <h6 class="mb-3">Child Products</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Product Name</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($children as $c): ?>
                                                <tr>
                                                    <td><?= isset($c['PART_ID']) ? (int)$c['PART_ID'] : ''; ?></td>
                                                    <td><?= isset($c['PART_NAME']) ? htmlspecialchars($c['PART_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                    <td><?= isset($c['PART_DESC']) ? htmlspecialchars($c['PART_DESC'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <?= isset($modal_logout) ? $modal_logout : ''; ?>
            </div>

            <?= isset($footer) ? $footer : ''; ?>
        </div>
    </div>

    <?= isset($foot) ? $foot : ''; ?>
</body>

</html>