flatpickr("#flatpickr", {
    "disable": [
        function(date) {
            return (date.getDay() === 0 || date.getDay() === 6);

        }
    ],
    "locale": {
        "firstDayOfWeek": 1 // start week on Monday
    },
    altInput: true,
    inline: true,
    altFormat: "d-m-Y",
});