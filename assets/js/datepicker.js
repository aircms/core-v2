$(document).ready(() => {
  const aTPS = {
    locale: {
      days: [
        locale('Sunday'),
        locale('Monday'),
        locale('Tuesday'),
        locale('Wednesday'),
        locale('Thursday'),
        locale('Friday'),
        locale('Saturday'),
      ],
      daysShort: [
        locale('Sun'),
        locale('Mon'),
        locale('Tue'),
        locale('Wed'),
        locale('Thu'),
        locale('Fri'),
        locale('Sat')
      ],
      daysMin: [
        locale('Su'),
        locale('Mo'),
        locale('Tu'),
        locale('We'),
        locale('Th'),
        locale('Fr'),
        locale('Sa'),
      ],
      months: [
        locale('January'),
        locale('February'),
        locale('March'),
        locale('April'),
        locale('May'),
        locale('June'),
        locale('July'),
        locale('August'),
        locale('September'),
        locale('October'),
        locale('November'),
        locale('December'),
      ],
      monthsShort: [
        locale('Jan'),
        locale('Feb'),
        locale('Mar'),
        locale('Apr'),
        locale('May'),
        locale('Jun'),
        locale('Jul'),
        locale('Aug'),
        locale('Sep'),
        locale('Oct'),
        locale('Nov'),
        locale('Dec'),
      ],
      today: locale('Today'),
      clear: locale('Clear'),
      dateFormat: 'yyyy-MM-dd',
      timeFormat: 'HH:mm',
      firstDay: 1
    },
    buttons: [locale('clear')]
  };

  const initAirDateTimePicker = (el, options = {}) => {
    let selectedDates = $(el).val();
    if (options.onlyTimepicker) {
      selectedDates = '2000-03-04 ' + selectedDates;
    }
    new AirDatepicker(el, {
      ...aTPS,
      ...options,
      ...{selectedDates}
    });
  };


  wait.on('[data-admin-datepicker]', (dP) => {
    const dateFormat = $(dP).data('admin-datetimepicker-format') ?? 'yyyy-MM-dd';

    initAirDateTimePicker(dP, {dateFormat});
  });

  wait.on('[data-admin-timepicker]', (dP) => {
    const timeFormat = $(dP).data('admin-datetimepicker-format') ?? 'HH:mm';

    initAirDateTimePicker(dP, {
      timepicker: true,
      onlyTimepicker: true,
      timeFormat
    });
  });
  wait.on('[data-admin-datetimepicker]', (dP) => {
    const dateFormat = $(dP).data('admin-datetimepicker-format') ?? 'yyyy-MM-dd HH:mm';

    initAirDateTimePicker(dP, {
      timepicker: true,
      dateFormat: dateFormat.split(' ')[0],
      timeFormat: dateFormat.split(' ')[1],
    });
  });
});