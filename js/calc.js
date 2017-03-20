'use strict';

    window.onload = () => {
        console.log("onload");
        $("#calc").on("input", function(){
            console.log("change");
            let value = this.value;
            console.log(value);
            $.ajax({
                "type": "POST",
                "url": "../calc.php",
                "data": {"math": value},
                "success": (res) => {
                    for(let i = 0; i < res.length; i++) {
                        console.log(res + " s");
                    }
                    $("#res")[0].innerHTML="Result: " + res;
                },
                "error": (res) => {
                    console.log(res + " e");
                    $("#res")[0].innerHTML="Result: " + res;
                }
            })
        })
    };

