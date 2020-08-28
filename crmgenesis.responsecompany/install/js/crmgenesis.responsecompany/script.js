BX.ready(function() {
    BX.ajax.runAction('crmgenesis:responsecompany.api.responsecompanyajax.setSections', {
        data: {
            sections: ['test23']
        }
    })
    .then(
        // Success
        function (response)
        {
            console.log(response);
        },
        // Failure
        BX.delegate(function (response)
        {
            console.log(response.errors);
        }, this)
    );
});
