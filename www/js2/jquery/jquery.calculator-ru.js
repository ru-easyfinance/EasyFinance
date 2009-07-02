/* http://keith-wood.name/calculator.html
   Spanish initialisation for the jQuery calculator extension
   Written by David Esperalta (http://www.davidesperalta.com) October 2008. */
(function($) { // hide the namespace

$.calculator.regional['ru'] = {
	decimalChar: '.', // Character for the decimal point 
	buttonText: '...', // Display text for trigger button 
	buttonStatus: 'Открыть калькулятор', // Status text for trigger button 
	closeText: 'Выход', // Display text for close link 
	closeStatus: 'Закрыть калькулятор', // Status text for close link 
	useText: 'Ок', // Display text for use link 
	useStatus: 'Использовать текущее значение', // Status text for use link 
	eraseText: 'Сброс', // Display text for erase link 
	eraseStatus: 'Очистить значение из поля', // Status text for erase link 
	backspaceText: '←', // Display text for backspace link 
	backspaceStatus: 'Удалить последний символ', // Status text for backspace link 
	clearErrorText: 'CE', // Display text for clear error link 
	clearErrorStatus: 'Erase the last number', // Status text for clear error link 
	clearText: 'CA', // Display text for clear link 
	clearStatus: 'Reset the calculator', // Status text for clear link 
	memClearText: 'MC', // Display text for memory clear link 
	memClearStatus: 'Очистить запомненное', // Status text for memory clear link 
	memRecallText: 'MR', // Display text for memory recall link 
	memRecallStatus: 'Восстановить запомненное значение', // Status text for memory recall link 
	memStoreText: 'MS', // Display text for memory store link 
	memStoreStatus: 'Запомнить значение', // Status text for memory store link 
	memAddText: 'M+', // Display text for memory add link 
	memAddStatus: 'Добавить в память', // Status text for memory add link 
	memSubtractText: 'M-', // Display text for memory subtract link 
	memSubtractStatus: 'Удалить из памяти', // Status text for memory subtract link 
	base2Text: 'Bin', // Display text for base 2 link 
	base2Status: 'Переключить на двоичную систему', // Status text for base 2 link 
	base8Text: 'Oct', // Display text for base 8 link 
	base8Status: 'Переключить на восьмеричную систему', // Status text for base 8 link 
	base10Text: 'Dec', // Display text for base 10 link 
	base10Status: 'Переключить на десятиричную', // Status text for base 10 link 
	base16Text: 'Hex', // Display text for base 16 link 
	base16Status: 'Переключить на шестнадцатеричную', // Status text for base 16 link 
	degreesText: 'Deg', // Display text for degrees link 
	degreesStatus: 'Переключить на градусы', // Status text for degrees link 
	radiansText: 'Rad', // Display text for radians link 
	radiansStatus: 'Переключить на радианы', // Status text for radians link
	showOn: 'opbutton', // 'focus' for popup on focus, 'button' for trigger button, 
    // 'both' for either, 'operator' for non-numeric character entered, 
    // 'opbutton' for operator/button combination 
    
//	buttonImage: '', // URL for trigger button image 
//	buttonImageOnly: false, // True if the image appears alone, false if it appears on a button 
	showAnim: 'show', // Name of jQuery animation for popup 
	showOptions: {}, // Options for enhanced animations 
	duration: 'fast', // Duration of display/closure 
	appendText: '$', // Display text following the input box, e.g. showing the format 
	calculatorClass: '', // Additional CSS class for the calculator for an instance 
	prompt: '', // Text across the top of the calculator 
	layout: this.standardLayout, // Layout of keys 
	base: 10, // The numeric base for calculations 
	precision: 10, // The number of digits of precision to use in rounding for display 
	useDegrees: false, // True to use degress for trigonometric functions, false for radians 
	constrainInput: true, // True to restrict typed characters to numerics, false to allow anything 
	onButton: null, // Define a callback function when a button is activated 
	onClose: null // Define a callback function when the panel is closed 	
};
$.calculator.setDefaults($.calculator.regional['ru']);

})(jQuery);