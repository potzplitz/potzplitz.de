$(document).ready(function () {

    const $jobrows = $('.jobRow');
    let counter = 0;
    const maxRuns = 500;

    const intervalId = setInterval(function () {

        if (counter >= maxRuns) {
            clearInterval(intervalId);
            return;
        }

        $jobrows.each(function () {
            const $row = $(this);
            const jobId = $row.data('jobname');

            $.ajax({
                url: '/api/admin/jobs/status',
                method: 'POST',
                data: { jobname: jobId },
                dataType: 'json',
                success: function (response) {
                    loadTableData(response, jobId);
                },
                error: function (xhr, status, error) {
                    console.error('Fehler bei JobRow ' + jobId, error);
                }
            });
        });

        counter++;

    }, 1000);

    $(document).on('click', '.startbutton', function () {
        const $btn = $(this);
        const $row = $btn.closest('.jobRow');
        const jobId = $row.data('jobname');

        if (!jobId || $btn.prop('disabled')) {
            return;
        }

        $btn.prop('disabled', true);

        startJob(jobId);
    });
});

function loadTableData(data, jobId) {
    const $row = $(".jobRow[data-jobname='" + jobId + "']");
    const status = Number(data.status);

    let statustext = "";

    if (status == 0) {
        statustext = `
            <span class="d-inline-flex align-items-center">
                <span style="
                    width:8px;
                    height:8px;
                    background-color:#dc3545;
                    border-radius:50%;
                    display:inline-block;
                    margin-right:6px;">
                </span>
                <span style='color:#dc3545;'>Inactive</span>
            </span>
        `;
    } else {
        statustext = `
            <span class="d-inline-flex align-items-center">
                <span style="
                    width:8px;
                    height:8px;
                    background-color:#28a745;
                    border-radius:50%;
                    display:inline-block;
                    margin-right:6px;">
                </span>
                <span style='color:#28a745;'>Active</span>
            </span>
        `;
    }

    $row.find('.jobStatus').html(statustext);
    $row.find('.jobStart').text(data.last_start ?? '—');
    $row.find('.jobEnd').text(data.last_finished ?? '—');

    let durationSeconds = null;

    if (status === 1 && data.last_start) {
        const startTs = parseGermanDateToTimestamp(data.last_start);
        if (startTs !== null) {
            durationSeconds = Math.floor((Date.now() - startTs) / 1000);
        }
    } else {
        durationSeconds = Number(data.duration) || null;
    }

    $row.find('.jobDuration').text(formatDuration(durationSeconds));

    const $btn = $row.find('.startbutton');

    if (status === 1) {
        $btn.addClass('hiddenIp').prop('disabled', true);
    } else {
        $btn.removeClass('hiddenIp').prop('disabled', false);
    }
}

function parseGermanDateToTimestamp(value) {
    const match = value.match(
        /^(\d{2})\.(\d{2})\.(\d{4}) (\d{2}):(\d{2})$/
    );

    if (!match) {
        return null;
    }

    const [, day, month, year, hour, minute] = match;

    const date = new Date(
        Number(year),
        Number(month) - 1,
        Number(day),
        Number(hour),
        Number(minute),
        0
    );

    return date.getTime();
}

function parseToTimestamp(value) {
    if (typeof value === 'number') {
        return value * 1000;
    }

    if (/^\d+$/.test(value)) {
        return parseInt(value, 10) * 1000;
    }

    if (typeof value === 'string') {
        const normalized = value.replace(' ', 'T');
        const parsed = Date.parse(normalized);
        if (!isNaN(parsed)) {
            return parsed;
        }
    }

    return null;
}

function formatDuration(seconds) {
    if (seconds === null || seconds === undefined || isNaN(seconds) || seconds < 0) {
        return '—';
    }

    seconds = Math.floor(seconds);

    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;

    const parts = [];
    if (h > 0) parts.push(h + ' Std');
    if (m > 0 || h > 0) parts.push(m + ' Min');
    parts.push(s + ' s');

    return parts.join(' ');
}

function startJob(jobId) {
    $.ajax({
        url: '/api/admin/jobs/start',
        method: 'POST',
        data: { jobname: jobId },
        success: function () {

        },
        error: function (xhr, status, error) {
            console.error('Fehler bei JobRow ' + jobId, error);
        }
    });
}




