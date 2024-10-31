jQuery(function($) {
    $(document).ready(function() {

        $("#wizard").steps({
            headerTag: "h4",
            bodyTag: "section",
            transitionEffect: "fade",
            enableAllSteps: true,
            transitionEffectSpeed: 500,
            onStepChanging: function (event, currentIndex, newIndex) { 
                if ( newIndex === 1 ) {
                    $('.steps ul').addClass('step-2');
                } else {
                    $('.steps ul').removeClass('step-2');
                }
                if ( newIndex === 2 ) {
                    $('.steps ul').addClass('step-3');
                } else {
                    $('.steps ul').removeClass('step-3');
                }
    
                if ( newIndex === 3 ) {
                    $('.steps ul').addClass('step-4');
                    $('.actions ul').addClass('step-last');
                } else {
                    $('.steps ul').removeClass('step-4');
                    $('.actions ul').removeClass('step-last');
                }
                return true; 
            },
            onFinished: function (event, currentIndex) {
                window.location = rbbqp_localized_strings.dashboard_url;
            },
            labels: {
                finish: "Go to dashboard",
                next: "Next",
                previous: "Previous"
            }
        });

        handlePagination();

        $("#rbbqpSettingsForm").on( 'submit', function(e) {
            e.preventDefault();
            $('#uploadingCsvMsg').show();

            $('#uploadingCsvMsg').html(rbbqp_localized_strings.processing_message);

            file_data = $('#csvFileToUploadInput').prop('files')[0];

            let formData = new FormData();

            formData.append('file', file_data);
            formData.append('action', 'call_wp_rbbqp_upload_csv');
            formData.append('security', 'call_wp_rbbqp_upload_csv');

            $.ajax({
                type: 'POST',
                url: rbbqp_localized_strings.ajax_url,
                data: formData,
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                success: function(res) {
                    if (res.success) {
                        
                        var successMsg = rbbqp_localized_strings.upload_success_message.replace( '%s', res.data );
                        $('#uploadingCsvMsg').html(successMsg);
                        $('#csvFileToUploadInput').val('');
                        $('button[type=submit]').prop('disabled', true);

                        rbbqp_fetchPage(1);
                        handlePagination();

                    } else {
                        $('#uploadingCsvMsg').html('Error: ' + res.data);
                    }

                }
            }).error(function(res) {
                $('#uploadingCsvMsg').html('Error: ' + res.data);
            });

        });

        $('#exportCsvBtn').on( 'click', function() {
            $.ajax({
                type: 'GET',
                url: rbbqp_localized_strings.ajax_url,
                data: { 
                    action: 'call_wp_rbbqp_export_csv',
                },
                success: function(data) {
                    response = JSON.parse(data);
                    rbbqp_downloadFile( document, response.url, 'custom-pricing.csv' );

                    // Go to next step.
                    $("#wizard-t-1").get(0).click();
                }
            }).error(function(err) {
                console.log('err', err);
            });
        });

        $('#exportProductsListBtn').on( 'click', function() {
            $('#exportProductsListDownloadMsg').show();
            $('#exportProductsListBtn').hide();
            $.ajax({
                type: 'GET',
                url: rbbqp_localized_strings.ajax_url,
                data: { 
                    action: 'call_wp_rbbqp_export_products_csv',
                },
                success: function(data) {
                    response = JSON.parse(data);
                    rbbqp_downloadFile( document, response.url, 'products-list.csv' );

                    $('#exportProductsListDownloadMsg').hide();
                    $('#exportProductsListBtn').show();
                }
            }).error(function(err) {
                $('#exportProductsListDownloadMsg').hide();
                $('#exportProductsListBtn').show();
                console.log('err', err);
            });
        });

        $('#exportRolesBtn').on( 'click', function() {
            $('#exportRolesDownloadMsg').show();
            $('#exportRolesBtn').hide();
            $.ajax({
                type: 'GET',
                url: rbbqp_localized_strings.ajax_url,
                data: { 
                    action: 'call_wp_rbbqp_export_roles',
                },
                success: function(data) {
                    response = JSON.parse(data);
                    rbbqp_downloadFile( document, response.url, 'roles.txt' );

                    $('#exportRolesDownloadMsg').hide();
                    $('#exportRolesBtn').show();
                }
            }).error(function(err) {
                $('#exportRolesDownloadMsg').hide();
                $('#exportRolesBtn').show();
                console.log('err', err);
            });
        });

        $('#csvFileToUploadInput').change(function() {
            $('button[type=submit]').prop('disabled', false);
        });

        if ( jQuery("#pricingLinesTable").length )
            rbbqp_fetchPage(1);

        
        function handlePagination() {

            var $pagination = $('#pagination-container');
            var defaultPaginationOptions = {
                totalPages: 20,
                visiblePages: 10,
                onPageClick: function (evt, page) {
                    rbbqp_fetchPage( page );
                }
            };
            $pagination.twbsPagination(defaultPaginationOptions);
            $.ajax({
                type: 'GET',
                url: rbbqp_localized_strings.ajax_url,
                data: { 
                    action: 'call_wp_rbbqp_get_total_pages',
                },
                success: function(data) {
                    response = JSON.parse(data.data);
                    var totalPages = response;
                    $pagination.twbsPagination('destroy');
                    $pagination.twbsPagination($.extend({}, defaultPaginationOptions, {
                        startPage: 1,
                        totalPages: totalPages
                    }));
                }
            }).error(function(err) {
                console.log('err', err);
            });
        }
    });


});

function rbbqp_fetchPage( page ) {

    jQuery('#rbbqpLoader').show();

    jQuery.ajax({
        type: 'POST',
        url: rbbqp_localized_strings.ajax_url,
        data: { 
            action: 'call_wp_rbbqp_get_pricing_lines',
            page: page
        },
        success: function(response) {
            if (response.success) {
                if (response.data.length) {

                    jQuery("#pricingLinesTable tbody tr").remove();

                    let table = document.getElementById("pricingLinesTable");
                    if ( !table )
                        return false;

                    let tableBody = table.getElementsByTagName('tbody')[0];

                    response.data.map(pricingLine => {
                        let row = tableBody.insertRow();
                        let productVariationId = row.insertCell(0);
                        productVariationId.innerHTML = pricingLine.product_variation_id;
                        let role = row.insertCell(1);
                        role.innerHTML = pricingLine.role;
                        let baseUnitPrice = row.insertCell(2);
                        baseUnitPrice.innerHTML = pricingLine.base_unit_price;
                        let thresholdUnitPrice = row.insertCell(3);
                        thresholdUnitPrice.innerHTML = pricingLine.threshold_unit_price;
                        let thresholdMinQty = row.insertCell(4);
                        thresholdMinQty.innerHTML = pricingLine.threshold_min_qty;
                     });  

                    jQuery('#rbbqpLoader').hide();
                } else {
                    jQuery('#rbbqpLoader').hide();
                    return false;
                }
                
            }
        }
    }).error(function(err) {
        console.log('err', err);
    });
}

function rbbqp_downloadFile( document, url, filename ) {

    var downloadLink = document.createElement("a");

    downloadLink.href = url + '/' + filename;
    downloadLink.download = filename;

    /*
    * Actually download CSV
    */
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
    
}