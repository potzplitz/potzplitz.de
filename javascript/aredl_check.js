$(document).ready(function() {
    $('.checkbutton').on('click', function() {
        let clicked_id = $(this).attr('id');
        let $button = $(this);
        let $container = $button.closest('.gdAREDLContainer');
        let valTextfield = $('input[data-for="' + clicked_id + '"]').val();
        let valLevelid = $('input[data-forlvl="' + clicked_id + '"]').data('levelid');

        let isCompleted = $container.hasClass('completed');
        let checked = isCompleted ? 0 : 1;

        if (isCompleted) {
            $container.removeClass('completed');
            $button.val('Check');
        } else {
            $container.addClass('completed');
            $button.val('Uncheck');
        }

        let formData = new FormData();
        formData.append('attempts', valTextfield);
        formData.append('completed', !isCompleted);
        formData.append('levelid', valLevelid);
        formData.append('progress', 100);
        formData.append('sart', "AREDL");
        formData.append('checked', checked);

        fetch('/api/submit/record', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Antwort vom Server:', data);
        })
        .catch(error => {
            console.error('Fehler beim Senden:', error);
        });
    });
});
