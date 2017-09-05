// Bootstrap File Input
$("#product-photos").fileinput({
    maxFilePreviewSize: 10240,
    showUpload: false,
    allowedFileExtensions: ["jpg", "jpeg", "png"]
});

$("#product-featured").fileinput({
    maxFilePreviewSize: 10240,
    showUpload: false,
    allowedFileExtensions: ["jpg", "jpeg", "png"]
});

// autoNumeric
numberFormat = AutoNumeric.multiple('.price-format > input', {
    decimalCharacter: ',',
    digitGroupSeparator: '.',
    decimalPlaces: 0
});

numberFormat2 = new AutoNumeric('#discount-percent', {
    maximumValue: 100,
    minimumValue: 0,
    decimalPlaces: 0
});

// Others
$("#disc-none-opt").change(function(){
    $("#disc-percent").hide(300);
    $("#disc-flat").hide(300);

    $("#discount-percent").removeAttr('required');
    $("#discount-flat").removeAttr('required');
});

$("#disc-percent-opt").change(function(){
    $("#disc-percent").show(300);
    $("#disc-flat").hide(300);

    $("#discount-percent").attr('required', true);
    $("#discount-flat").removeAttr('required');
});

$("#disc-flat-opt").change(function(){
    $("#disc-flat").show(300);
    $("#disc-percent").hide(300);

    $("#discount-flat").attr('required',true);
    $("#discount-percent").removeAttr('required');
});

// Edit Product
function makeFeatured(id){
    var el = document.getElementsByClassName('cover-item');
    for(var i = 0; i < el.length; i++){
        var element = el[i];
        element.style.borderColor = "#73879C";
    }

    var btnCoverList = document.getElementsByClassName('btn-cover-toggle');
    if(btnCoverList.length >= 2){
        for(var i = 0; i < btnCoverList.length; i++){
            var element = btnCoverList[i];
            if(element.innerHTML === "Undo"){
                element.style.borderColor = "#73879C";

                var tmpId = (element.id).split('_');
                var deleteBtnId = tmpId[0] + "_btn_delete";
                $("#" + deleteBtnId).removeAttr('disabled');
                document.getElementById(deleteBtnId).dataset.disabled = "false"
            }
            element.innerHTML = "Make Featured"
        }
    }

    var btnContent = $("#" + id + "_btn_toggle").html();
    var selectedEl = document.getElementById(id + "_img");
    if(btnContent == "Make Featured"){
        selectedEl.style.borderColor = "red";
        $("#" + id + "_btn_toggle").html("Undo");
        $("#" + id + "_btn_delete").attr('disabled','disabled');

        document.getElementById(id + "_btn_delete").dataset.disabled = "true"
    }
    else{
        selectedEl.style.borderColor = "#73879C";
        $("#" + id + "_btn_delete").removeAttr('disabled');
        document.getElementById(id + "_btn_delete").dataset.disabled = "false"
        document.getElementById(id + "_btn_toggle").innerHTML = "Make Featured"
    }
}

function deleteImageEdit(id){
    var deleteBtn = $("#" + id + "_btn_delete");

    var isDisabled = deleteBtn.attr('data-disabled');

    if(isDisabled === "false"){
        var hiddenVal = $("#deleted_img_id").val();
        if(hiddenVal == ''){
            $("#deleted_img_id").val(id);
        }else {
            $("#deleted_img_id").val(hiddenVal + "," + id);
        }

        $("#" + id + "_img").remove();
    }
}
