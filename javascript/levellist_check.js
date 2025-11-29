$(document).ready(function() {
    $('.checkbutton').on('click', function() {
        let clicked_id = $(this).attr('id');
        let $button = $(this);
        let $container = $button.closest('.gdAREDLContainer');
        let valTextfield = $('input[data-for="' + clicked_id + '"]').val();
        let valLevelid = $('input[data-forlvl="' + clicked_id + '"]').data('levelid');
        let sart = $('.transfer_sart').data('sart');

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
        formData.append('sart', sart);
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

    $('#check_levels').on('click', function(e) {
        let checked = $('#hidden_checked').val();
        checked = (checked === 'true') ? 'false' : 'true';
        $('#hidden_checked').val(checked);

        if(checked === 'true') {
            $('#hidden_uncompleted').val('false');
        }
        
        $('#searchbar').submit();
    });
    
    $('#view_unchecked_levels').on('click', function(e) {
        let checked = $('#hidden_uncompleted').val();
        checked = (checked === 'true') ? 'false' : 'true';
        $('#hidden_uncompleted').val(checked);

        if(checked === 'true') {
            $('#hidden_checked').val('false');
        }
        
        $('#searchbar').submit();
    });

    document.querySelectorAll('.gdAREDLContainer input').forEach(el => {
        el.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
    });

});
