document.addEventListener('DOMContentLoaded', function() {
    const addEventBtn = document.getElementById('addEventBtn');
    const addEventForm = document.getElementById('addEventForm');
    const closePopupBtn = document.getElementById('closePopupBtn');

    addEventBtn.addEventListener('click', function() {
        addEventForm.style.display = 'block';
    });

    closePopupBtn.addEventListener('click', function() {
        addEventForm.style.display = 'none';
    });
});
