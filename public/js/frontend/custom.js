$('#example').DataTable();

function addToCart(productId){
    var color = '';
    if($('#select-color').length > 0){
        color = $('#select-color').val();
    }
    var size = '';
    if($('#select-size').length > 0){
        size = $('#select-size').val();
    }

    $.ajax({
        url     : urlLink,
        method  : 'POST',
        data    : {
            // _token: CSRF_TOKEN,
            product_id  : productId,
            color : color,
            size:  size
        },
        headers:
        {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success : function(response){
            if(response.success === true){
                $("#add-cart-modal").modal()
            }
            else{
                if(response.error === "login"){
                    var redirect = window.location.href;
                    window.location = "/login?redirect=" + redirect;
                }
                else{
                    alert("Out of Stock");
                }
            }
        },
        error:function(){

        }
    });
}

function deleteCart(cartId){
    $.ajax({
        url     : urlLinkDelete,
        method  : 'POST',
        data    : {
            cart_id  : cartId
        },
        headers:
            {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        success : function(response){
            $("#cart_item_" + cartId).fadeOut("normal", function() {
                $(this).remove();
            });

            $priceTemp = $("total-price-value").val();
            $newPrice = $priceTemp - response;
            $("total-price-value").val($newPrice);


        },
        error:function(){
            alert("error!!!!");
        }
    });
}

function editCartQuantity(cartId){
var quantity = $('#cart_quantity_'+cartId).val();
var productSubtotal = '#product-subtotal-' + cartId;
if(quantity){
    $.ajax({
        url     : urlLinkEdit,
        method  : 'POST',
        dataType: 'JSON',
        data    : {
            cart_id  : cartId,
            quantity: quantity
        },
        headers:
        {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success : function(response){
            var newSinglePrice = "Rp. " + response.singlePrice;
            $(productSubtotal).html(newSinglePrice);

            var newtotalPrice = "Rp. " + response.totalPrice;
            $('#sub-total-price').html(newtotalPrice);
            $('#total-price').html(newtotalPrice);
        },
        error:function(){

        }
    });
}
}

// autoNumeric
numberFormat = AutoNumeric.multiple('.price-format > input', {
    decimalCharacter: ',',
    digitGroupSeparator: '.',
    decimalPlaces: 0
});

//SELECT PAYMENT
function handleChangePayment(myRadio){
    var selectedValue = myRadio.value;
    var grandTotalValue = $("#grand-total-value").val();
    grandTotalValue = grandTotalValue.replace(/[.]/g, "");
    var newGrandTotalValue = 0;
    var newGrandTotal = "";
    var selectedFeeValue = $("#selected-fee").val();
    selectedFeeValue = selectedFeeValue.replace(/[.]/g, "");
    var selectedFee = "";

    if(selectedValue == "bank_transfer"){
        newGrandTotalValue = parseInt(grandTotalValue) - parseInt(selectedFeeValue) + 4000;
        newGrandTotal = addCommas(newGrandTotalValue);

        $("#selected-fee").val(4000);
        $('#admin-fee').html(addCommas(4000));
        $("#grand-total-value").val(newGrandTotalValue);
        $("#grand-total-price").html(newGrandTotal);
    }
    else{
        var fee = ((parseInt(grandTotalValue) - parseInt(selectedFeeValue)) * 0.03) + 2000;
        selectedFee = addCommas(fee);
        newGrandTotalValue = parseInt(grandTotalValue) - parseInt(selectedFeeValue) + fee;
        newGrandTotal = addCommas(newGrandTotalValue);

        $("#selected-fee").val(fee);
        $('#admin-fee').html(selectedFee);
        $("#grand-total-value").val(newGrandTotalValue);
        $("#grand-total-price").html(newGrandTotal);
    }
}


function addCommas(nStr) {
    nStr += '';
    x = nStr.split(',');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + '.' + '$2');
    }
    return "Rp " + x1 + x2;
}

// SELECTIONS IN PRODUCT DETAIL
function onchangeWeight(e){
    var price = e.options[e.selectedIndex].getAttribute('data-price');
    $('#price-label').html("Rp " + price);
}

function onchangeSize(e){
    var obj = e.options[e.selectedIndex];
    if(obj.hasAttribute("data-price")){
        var price = obj.getAttribute('data-price');
        if(price !== '0'){
            $('#price-label').html("Rp " + price);
        }
    }

    if(obj.hasAttribute("data-weight")){
        var weight = obj.getAttribute('data-weight');
        if(weight !== 0){
            $('#weight-label').html("Weight: " + weight + " Kg");
        }
    }
}