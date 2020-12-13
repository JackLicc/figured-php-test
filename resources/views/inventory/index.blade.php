<html>
<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        html, body {
            unset: all;
            font-family: Verdana,sans-serif;
            font-size: 15px;
            line-height: 1.5;
        }
        .main-container {
            text-align: center;
            margin-top: 200px;
        }
        .logo-section > img {
            width: 200px;
        }
        #alertWarning, #alertSuccess {
            width: 50%;
            margin: 0 auto;
        }
        .query-section {
            width: 50%;
            margin: 0 auto;
            margin-top: 50px;
        }
    </style>
</head>
<body>
<div class="main-container">
    <div class="logo-section">
        <img src="https://www.figured.com/hubfs/Figured_October2019/Images/figured-logo.png" />
    </div>

    <div class="query-section input-group mb-3">
        <input type="text" class="form-control" placeholder="Please enter the quantity of fertiliser, <?=$availableQuantity?> available.">
        <button class="btn btn-outline-secondary" type="button" id="queryButton">Query</button>
    </div>

    <div id="alertWarning" class="alert alert-warning alert-dismissible fade">
        <strong>Warning!</strong> Insufficient quantity on hand.
        <button type="button" class="closeAlertWarning">&times;</button>
    </div>

    <div id="alertSuccess" class="alert alert-success alert-dismissible fade">
        <strong><span class="application-valuation"></span></strong>
        <button type="button" class="closeAlertSuccess">&times;</button>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('input[type="text"]').on('input', function() {
            let quantity = $(this).val().trim()
            if (!/^[0-9]+$/.test(quantity) || parseInt(quantity) <= 0) {
                $('#queryButton').attr('disabled', true)
            } else {
                $('#queryButton').removeAttr('disabled')
            }
        });
        $('#queryButton').click(function() {
            let availableQty = parseInt("<?=$availableQuantity?>")

            let requestedQty = $('input[type="text"]').val().trim()
            requestedQty = parseInt(requestedQty)
            if (requestedQty > availableQty) {
                $('#alertWarning').addClass('show')
            }

            $.get(`/inventory/query/${requestedQty}`, function(res) {
                if (res.valuation) {
                    $('#alertSuccess').addClass('show')
                    $('.application-valuation').text(`$${res.valuation} of the quantity of fertiliser that will be applied`)
                }
            })
        })

        $('.closeAlertSuccess').click(function() {
            $('#alertSuccess').removeClass('show')
        })

        $('.closeAlertWarning').click(function() {
            $('#alertWarning').removeClass('show')
        })
    })


</script>
</body>
</html>
