
//### MAIN DASHBOARD LOGIC

jQuery(document).ready(function($) {

    // Event listener for sidebar links
    $('.load-content').on('click', function(e) {
        e.preventDefault();  // Prevent the default behavior of the link

        var contentType = $(this).data('content-type');  // Get the content type from the data attribute

        $.ajax({
            url: ajax_params.ajax_url,  // URL for the AJAX handler (set through wp_localize_script)
            type: 'POST',
            data: {
                action: 'load_content',  // Action hook name
                content_type: contentType  // The type of content we're requesting
            },
            success: function(response) {
                $('#mainContainer').html(response);  // Insert the received content into the mainContainer div
                $('[data-toggle="tooltip"]').tooltip();
            },
            error: function() {
                console.log(response);
                console.error('Failed to load content.');
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", function() {
    let menuItems = document.querySelectorAll('.menu-item');
  
    menuItems.forEach(function(item) {
      item.addEventListener('click', function() {
        // Remove active class from other items
        menuItems.forEach(function(innerItem) {
          innerItem.classList.remove('active');
        });
  
        // Add active class to the clicked item
        item.classList.add('active');
      });
    });
  });
  

//### FILES LOGIC

    jQuery(document).ready(function($) {
        $('#mainContainer').on('submit', '#file-upload-form', function(e) {
            var fileInput = $(this).find('input[type="file"]');
            var file = fileInput[0].files[0];
            var maxSize = 10 * 1024 * 1024; // 10MB in bytes

            if (file && file.size > maxSize) {
                $('.toast-body').text('File is too large. Maximum allowed size is 10MB.');
                $('#myToast').toast('show');
                e.preventDefault(); // prevent form submission
                return;
            }

            e.preventDefault();

            var formData = new FormData(this);
            formData.append('action', 'handle_file_upload'); // Specify the AJAX action here

            $.ajax({
                type: "POST",
                url: ajax_params.ajax_url, // Using localized script variable
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('.load-content[data-content-type="plantillas"]').click(); // Load only the Plantillas section again
                    $('.toast-body').text('File uploaded successfully.');
                    $('#myToast').toast('show');
                },
                error: function() {
                    $('.toast-body').text('Error while uploading file.');
                    $('#myToast').toast('show');
                }
            });
        });
    });

    //# PROVEEDORES Y CONTRATISTAS LOGIC

    // Event delegation for edit-button
    document.body.addEventListener('click', function(event) {
        if (event.target.matches('.edit-button')) {
            var button = event.target;
            var row = button.closest('tr');
            
            // Save current data
            row.dataset.originalData = JSON.stringify(Array.from(row.querySelectorAll('.editable')).map(cell => cell.textContent));

            row.querySelectorAll('.editable').forEach(function(cell) {
                var input = document.createElement('input');
                input.value = cell.textContent;
                input.dataset.field = cell.dataset.field;
                cell.textContent = '';
                cell.appendChild(input);
            });
            button.style.display = 'none';
            row.querySelector('.cancel-button').style.display = 'inline';
            row.querySelector('.submit-button').style.display = 'inline';
        }
    });

    // Event delegation for cancel-button
    document.body.addEventListener('click', function(event) {
        if (event.target.matches('.cancel-button')) {
            var button = event.target;
            var row = button.closest('tr');
            
            // Revert changes using original data
            var originalData = JSON.parse(row.dataset.originalData);
            row.querySelectorAll('.editable').forEach(function(cell, index) {
                cell.textContent = originalData[index];
            });

            row.querySelector('.edit-button').style.display = 'inline';
            button.style.display = 'none';
            row.querySelector('.submit-button').style.display = 'none';
        }
    });

    // Event delegation for submit-button
    document.body.addEventListener('click', function(event) {
        if (event.target.matches('.submit-button')) {
            var button = event.target;
            var row = button.closest('tr');
            var data = {};
        
            row.querySelectorAll('.editable').forEach(function(cell) {
                var input = cell.querySelector('input');
                data[input.dataset.field] = input.value;
            });
        
            data.id = row.id.split('-')[1];
            data.form_type = document.querySelector('input[name="form_type"]').value;
            
            // Additional validation for the ID
            if (!data.id || isNaN(data.id)) {
                console.error("Invalid row ID:", row.id);
                return;
            }
        
            sendDataToServer(data, row);
        }
    });

    document.body.addEventListener('submit', function(event) {
        if (event.target.matches('#myform')) {
            event.preventDefault();
            submitFormData();
        }
    });


    function revertChanges(row) {
        row.querySelectorAll('.editable').forEach(function(cell) {
            var input = cell.querySelector('input');
            cell.textContent = input.value;
        });
        row.querySelector('.edit-button').style.display = 'inline';
        row.querySelector('.cancel-button').style.display = 'none';
        row.querySelector('.submit-button').style.display = 'none';
    }

    function sendDataToServer(data, row) {
        var encodedData = `action=update_data&Nombre=${encodeURIComponent(data.Nombre)}&Empresa=${encodeURIComponent(data.Empresa)}&Especialidad=${encodeURIComponent(data.Especialidad)}&Telefono=${encodeURIComponent(data.Telefono)}&Email=${encodeURIComponent(data.Email)}&Comentarios=${encodeURIComponent(data.Comentarios)}&form_type=${encodeURIComponent(data.form_type)}&id=${encodeURIComponent(data.id)}&_ajax_nonce=${encodeURIComponent(ajax_params.nonce)}`;
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajax_params.ajax_url, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (this.readyState === 4) {
                if (this.status === 200) {
                    var response = JSON.parse(this.responseText);
                    if (response.success) {
                        revertChanges(row); // revert changes in the UI on successful update

                        
                        // Trigger the click event for the relevant content type

                        showToast('Update successful!!!', 'success');
                    } else {
                        console.error("Update failed:", response.data);
                        showToast('Update failed: ' + response.data, 'danger');
                    }
                } else {
                    console.error('Server returned status:', this.status);
                    showToast('Server Error', 'danger');
                }
            }
        };
        xhr.send(encodedData);
    }

    function submitFormData() {
        var form = document.querySelector('#myform');
        var formData = new FormData(form);
        formData.append('action', 'submit_form_data');
        
        // Add the nonce for security
        formData.append('_ajax_nonce', ajax_params.nonce);

        sendFormDataToServer(formData);
    }
    function sendFormDataToServer(data) {
        var formType = data.get('form_type'); // Extract value from FormData
        console.log(data)
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajax_params.ajax_url, true);
        xhr.onreadystatechange = function() {
            if (this.readyState === 4) {
                if (this.status === 200) {
                    var response = JSON.parse(this.responseText);
                    if (response.success) {
                        console.log(formType)
                        // Trigger the click event for the relevant content type
                        $(".load-content[data-content-type="+formType+"]").click();
                        document.querySelector('#myform').reset();
                        showToast('Update successful!!', 'success');

                    } else {
                        showToast('Update failed: ' + response.data, 'danger');
                        alert('Submission failed: ' + response.data); 
                    }
                } else {
                    console.error('Server returned status:', this.status);
                    showToast('Server Error', 'danger');
                }
            }
        };
        xhr.send(data);
    }

    $(document).on('keyup', '#filterEspecialidad', function() {
        var value = $(this).val().toLowerCase();
        
        // Iterate through each row in the table
        $('table tbody tr').each(function() {
            var especialidad = $(this).find('td[data-field="Especialidad"]').text().toLowerCase();
            
            // Check if the row's Especialidad value contains the filter input
            if (especialidad.indexOf(value) > -1) {
                $(this).show(); // Show the row if it matches
            } else {
                $(this).hide(); // Hide the row if it doesn't match
            }
        });
    });

    function showToast(message, type) {

        const toastElement = document.getElementById('myToast');
        if (toastElement) {
            // Set toast body content
            toastElement.querySelector('.toast-body').textContent = message;
            
            // Add a class based on type for coloring (optional)
            toastElement.querySelector('.toast-body').classList.remove('bg-success', 'bg-danger'); // Remove existing bg classes
            if (type === 'success') {
                toastElement.querySelector('.toast-body').classList.add('bg-success');
                toastElement.style.display = 'block';

            } else if (type === 'danger') {
                toastElement.querySelector('.toast-body').classList.add('bg-danger');
            }
            
            // Display the toast
            setTimeout(() => $(toastElement).toast('show'), 100);

        }
    }


//### UNIT PRICES LOGIC

jQuery(document).on('input', '#unitprice_search', function() {
    console.log("searching")
    var searchValue = jQuery(this).val();
    jQuery.ajax({
        url: ajax_params.ajax_url,
        type: 'post',
        data: {
            action: 'filter_unit_prices',
            search: searchValue,
            nonce: ajax_params.nonce
        },
        success: function(response) {
            // Update the list with the new results
            jQuery('#aux').html(response);
        }
    });
});

jQuery(document).ready(function($) {
    
    // Event listener for parent rows only (excluding detail rows)
    $("body").on("click", "#unitPriceTable tbody tr:not(.details-row)", function(e) {
        e.stopPropagation();
    
        var unitPriceId = $(this).find("td:first").text();
        var targetID = "#details-" + unitPriceId;
        var detailRow = $(targetID);
    
        if (detailRow.is(':visible')) {
            console.log("already open")
            detailRow.slideUp(300);
            return;
        }
    
        // Hide all other detail rows
        $(".details-row:not(" + targetID + ")").slideUp(300);
    
        $.ajax({
            url: ajax_params.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'fetch_unit_price_details',
                unitPriceId: unitPriceId,
                nonce: ajax_params.nonce
            },
            success: function(data) {

                var htmlOutput = buildDetailsTable(data);
                detailRow.find('td').attr('colspan', '7').html(htmlOutput);
                detailRow.slideDown(300);
            },
            error: function() {
                console.error("There was an error fetching the details.");
            }
        });
    });
    

    function buildDetailsTable(data) {
        var htmlOutput = "";
        htmlOutput += `<table class='table details-subtable' style='width: 100%;'>`;
        htmlOutput += "<thead><tr><th>N°</th><th>Nombre</th><th>Unidad</th><th>Rendimiento</th><th>Precio</th><th>Precio Unitario</th></tr></thead>";
        htmlOutput += "<tbody>";
        
        var globalIndex = 1;
    
        for (var category in data) {
            if (data[category].length > 0) { 
                const categoryMapping = {
                    "Materials": "Materiales",
                    "Equipment": "Arriendo de equipos",
                    "Labor": "Mano de obra / Subcontratos"
                };
                
                let categoria = categoryMapping[category] || "Otros";
                
                htmlOutput += `<tr class='category-title-row'><td colspan='6'><strong>${categoria}</strong></td></tr>`;
                
                data[category].forEach((item) => {
                    var price = parseFloat(item.price);
                    var singlePrice = parseFloat(item.single_price);
                    var quantity = parseFloat(item.quantity);
                    var formattedQuantity;
                    if (isNaN(quantity)) {
                        console.error("Error: Invalid quantity value", item.quantity);
                        formattedQuantity = "0.000"; // or whatever default you want
                    } else {
                        formattedQuantity = quantity.toFixed(3);
                    }
                    if (isNaN(price)) {
                        console.error("Error: Invalid price value", item.price);
                        price = 0;
                    }
                    if (isNaN(singlePrice)) {
                        console.error("Error: Invalid single price value", item.single_price);
                        singlePrice = 0;
                    }
                    var formattedPrice = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(price);
                    var formattedSinglePrice = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(singlePrice);
                    console.log(item.unit);
                    htmlOutput += `<tr>
                        <td>${globalIndex++}</td>
                        <td>${item.name}</td>
                        <td>${item.unit}</td>
                        <td>${formattedQuantity}</td>
                        <td>${formattedSinglePrice}</td>
                        <td>${formattedPrice}</td>
                    </tr>`;
                });
            }
        }

        htmlOutput += "</tbody></table>";
        return htmlOutput;
    }

});


//resources
jQuery(document).ready(function($) {
    jQuery(document).on('input', 'input[id$="_search"]', function() {
        console.log("searching")
        var resourceType = this.id.replace('_search', ''); // Extract the resource type from the input id
        var searchValue = jQuery(this).val();
        jQuery.ajax({
            url: ajax_params.ajax_url,
            type: 'post',
            data: {
                action: 'filter_resources',
                resource_type: resourceType,
                search: searchValue,  // This will send the name you want to filter by
                nonce: ajax_params.nonce
            },
            success: function(response) {
                // Update the list with the new results
                jQuery('#aux_' + resourceType).html(response);
            }
        });
    });
});

document.body.addEventListener('submit', function(event) {
    if (event.target.matches('#resourceForm_material') || event.target.matches('#resourceForm_labor') || event.target.matches('#resourceForm_equipment') || event.target.matches('#resourceForm_others')) {
        event.preventDefault();
        submitResourceData();
    }
});

function submitResourceData() {
    var form = document.querySelector('[id^="resourceForm_"]'); // Select form with ID starting with "resourceForm_"
    var formData = new FormData(form);
    formData.append('action', 'submit_resource_data');
    
    // Add the nonce for security
    formData.append('_ajax_nonce', ajax_params.nonce);

    sendResourceDataToServer(formData);
}

function sendResourceDataToServer(data) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', ajax_params.ajax_url, true);
    xhr.onreadystatechange = function() {
        if (this.readyState === 4) {
            if (this.status === 200) {
                var response = JSON.parse(this.responseText);
                if (response.success) {
                    // Trigger the click event for the relevant content type
                    
                    $(".load-content[data-content-type=" + data.get('form_type') + "]").click();
                    document.querySelector('#resourceForm_' + data.get('form_type')).reset();

                    showToast('Insert successful!!', 'success');
                } else {
                    showToast('Insert failed: ' + response.data, 'danger');
                    alert('Submission failed: ' + response.data); 
                }
            } else {
                console.error('Server returned status:', this.status);
                showToast('Server Error', 'danger');
            }
        }
    };
    xhr.send(data);
}
