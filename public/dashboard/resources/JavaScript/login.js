"use strict";
$(function () {
    $('#form-login #btn-login').on('submit', function () {
        const data = $("#form-login").serialize();
        $.ajax({
            type: 'POST',
            url: "/api/v2/users/login?cookie",
            data: data,
            async: true,
            contentType: "javascript/json",
            dataType: "json",
            success: function (response) {
                console.log(response);
            }
        });
    });
});
//# sourceMappingURL=login.js.map