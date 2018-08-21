$('document').ready(() => {


    $('#btnEnviar').click(() => {
        var num_columns = $('#excel th').length;
        var columns = [];
        var datos = [];
        var error = "";
        for (let i = 0; i < num_columns; i++) {
            const element = "#column" + i;
            datos.push($(element).val());
            if ($(element).val() != "") {
                if (columns.indexOf($(element).val()) < 0) {
                    columns.push($(element).val());
                } else {
                    error = "Error al seleccionar las columnas";
                } 
            }
        }
        if (error == "") {
            //petición ajax al servidor
            var ruta = "ajax.php";
            $.ajax({
                type: "post",
                data: { "columnas": datos },
                dataType: "json",
                url: ruta,
                success: function (response) {
                    $('#validaciones').html(response["resultInsert"]+"<br>"+response["resultUpdate"]);
                    console.log(response);
                },
                error: function (param) { 
                    console.log(param);
                 }
            });
        } else {
            $('#validaciones').html(error);

        }


    });
})