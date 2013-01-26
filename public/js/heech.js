/* 
    Document   : heech
    Created on : 12:50:20 PM
    Author     : Yariv Katz
    Copyright  : Nerdeez.com Ltd.
    Description:
        main js functions for heechikerz.com
*/

/**
 * will init the common actions
 */
function initCommonActions(){
    //init the span text helper
    $('.text-input').on('keyup' , function(){
        ksSetTextHelper($(this));
    });
    
    //init the date picker
    $('#details_input_date').datepicker(
            { 
        dateFormat: "dd/mm/yy",
        minDate:0,
        showOptions: { direction: "down" },
        onSelect: function(){
            ksSetTextHelper($('#details_input_date'));
        }
    });

    //init the time picker
    $('#details_input_hour').timepicker({
        // Options
        timeSeparator: ':',           // The character to use to separate hours and minutes. (default: ':')
        showLeadingZero: true,        // Define whether or not to show a leading zero for hours < 10.
        showMinutesLeadingZero: true, // Define whether or not to show a leading zero for minutes < 10.
        showPeriod: false,            // Define whether or not to show AM/PM with selected time. (default: false)
        showPeriodLabels: true,       // Define if the AM/PM labels on the left are displayed. (default: true)
        periodSeparator: ' ',         // The character to use to separate the time from the time period.
        defaultTime: 'now',         // Used as default time when input field is empty or for inline timePicker
        // trigger options
        showOn: 'both',              // Define when the timepicker is shown.
                                      // 'focus': when the input gets focus, 'button' when the button trigger element is clicked,
                                      // 'both': when the input gets focus and when the button is clicked.
        button: '#details_button_time',                 // jQuery selector that acts as button trigger. ex: '#trigger_button'

        // Localization
        hourText: 'Hour',             // Define the locale text for "Hours"
        minuteText: 'Minute',         // Define the locale text for "Minute"
        amPmText: ['AM', 'PM'],       // Define the locale text for periods

        // Position
        myPosition: 'left top',       // Corner of the dialog to position, used with the jQuery UI Position utility if present.
        atPosition: 'left bottom',    // Corner of the input to position

        // Events

        // custom hours and minutes
        hours: {
            starts: 0,                // First displayed hour
            ends: 23                  // Last displayed hour
        },
        minutes: {
            starts: 0,                // First displayed minute
            ends: 55,                 // Last displayed minute
            interval: 5               // Interval of displayed minutes
        },
        rows: 4,                      // Number of rows for the input tables, minimum 2, makes more sense if you use multiple of 2
        showHours: true,              // Define if the hours section is displayed or not. Set to false to get a minute only dialog
        showMinutes: true,            // Define if the minutes section is displayed or not. Set to false to get an hour only dialog

        // buttons
        showCloseButton: false,       // shows an OK button to confirm the edit
        closeButtonText: 'Done',      // Text for the confirmation button (ok button)
        showNowButton: false,         // Shows the 'now' button
        nowButtonText: 'Now',         // Text for the now button
        showDeselectButton: false,    // Shows the deselect time button
        deselectButtonText: 'Deselect' // Text for the deselect button

    });
}

/**
 * make the text inside the input disappear
 * @param e from here i can extract the textarea we are checking
 */
function ksSetTextHelper(e){
    if(e.val().length > 0){
        e.parent().addClass("hasome");
    }
    else{
        e.parent().removeClass("hasome");
        e.parent().children('span').css('z-index','0');
    }
}

/**
 * shows the calendar
 * @returns {null}
 */
function showCaledar(){
     $('#details_input_date').datepicker("show");
}