function api(url, param, callback, headers = {}) {
    headers['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        url: url,
        type: 'POST',
        data: param,
        headers: headers,
        success: function (response) {
            callback(response);
        },
        error: function (jqXHR) {
            if (jqXHR.status === 401) {
                window.location.href = window.location.origin + '/logout';
            }
        }
    });
}

