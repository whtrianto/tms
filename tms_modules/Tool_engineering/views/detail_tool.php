<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head; ?>
    <style>
        html,
        body,
        #content-wrapper {
            color: #000;
        }

        .card,
        label,
        .form-text {
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
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="m-0 font-weight-bold text-primary">Tool Details</h4>
                            <div>
                                <a href="<?= base_url('tool_engineering/tool'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                    <i class="fa fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <p><strong>Tool:</strong> <?= htmlspecialchars($tool['TOOL_NAME']); ?></p>
                                    <p><strong>Type:</strong> <?= htmlspecialchars($tool['TOOL_TYPE']); ?></p>
                                    <p><strong>Description:</strong> <?= htmlspecialchars($tool['TOOL_DESC']); ?></p>
                                </div>
                            </div>
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