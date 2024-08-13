/**
 * @since version 0.84
 *
 * @param target
 * @param fields
**/
function submitAlert(target,fields) {

   //  var myForm    = document.createElement("form");
   //  myForm.method = "post";
   //  myForm.action = target;
   // for (var name in fields) {
   //     var myInput = document.createElement("input");
   //     myInput.setAttribute("name", name);
   //     myInput.setAttribute("value", fields[name]);
   //     myForm.appendChild(myInput);
   // }
   //  myForm.target = "_blank";
   //  document.body.appendChild(myForm);
   //  myForm.submit();
   //  document.body.removeChild(myForm);
    // Usage!

    $.ajax({
        url:  CFG_GLPI.root_doc +"/plugins/ticketalerts/ajax/updateAlert.php",
        type: "POST",
        data: fields,
        success: function(ret)
        {
            console.log(ret);
            if(ret != 0) {
                window.open(ret, '_blank');
            } else {
                location.reload()
            }

            // window.location.reload()
            $.ajax({
                url:  CFG_GLPI.root_doc +"/plugins/ticketalerts/ajax/reloadAlert.php",
                type: "POST",
                data: { "id": $('#personal-tabs li a').not('.inactive').attr('id')},
                success: function(ret)
                {

                    $('#alert_block').html(ret);
                    // window.location.reload()
                }
            });
        }
    });


    // window.location.reload();

}

// sleep time expects milliseconds
function sleep (time) {
    return new Promise((resolve) => setTimeout(resolve, time));
}


