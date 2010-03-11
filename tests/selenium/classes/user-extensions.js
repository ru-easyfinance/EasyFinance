/**
 * Checks if jQuery framework is used.
 *
 * @return If jQuery is attached returns true, else returns false.
 */
Selenium.prototype.isJqueryUsed = function() {
    return typeof(this.browserbot.getUserWindow().jQuery) == 'function';
};

/**
 * Checks if Prototype framework is used.
 *
 * @return If Prototype is attached returns true, else returns false.
 */
Selenium.prototype.isPrototypeUsed = function() {
    return typeof(this.browserbot.getUserWindow().Ajax) == 'function';
};

/**
 * Checks if Dojo framework is used.
 *
 * @return If Dojo is attached returns true, else returns false.
 */
Selenium.prototype.isDojoUsed = function() {
    return typeof(this.browserbot.getUserWindow().dojo) == 'function';
};

/**
 * Waits for all active AJAX requests to finish during specified timeout. Works only for AJAX requests which are
 * instantiated using one of the following frameworks: jQuery, Prototype, Dojo. Don't work (immediately returns without
 * any errors) if standart AJAX API is used to send request.
 *
 * @param timeout Timeout in milliseconds.
 * @throws SeleniumError If timeout is reached.
 */
Selenium.prototype.doWaitForAjaxRequests = function(timeout) {
    if (this.isJqueryUsed()) {
        return this.doWaitForJqueryAjaxRequests(timeout);
    }
    if (this.isPrototypeUsed()) {
        return this.doWaitForPrototypeAjaxRequests(timeout);
    }
    if (this.isDojoUsed()) {
        return this.doWaitForDojoAjaxRequests(timeout);
    }
};

/**
 * Waits for all active jQuery AJAX requests to finish.
 *
 * @param timeout Timeout in milliseconds.
 * @throws SeleniumError If timeout is reached.
 */
Selenium.prototype.doWaitForJqueryAjaxRequests = function(timeout) {
    return Selenium.decorateFunctionWithTimeout(function() {
        return selenium.browserbot.getUserWindow().jQuery.active == 0;
    }, timeout);
};

/**
 * Waits for all active Prototype AJAX requests to finish.
 *
 * @param timeout Timeout in milliseconds.
 * @throws SeleniumError If timeout is reached.
 */
Selenium.prototype.doWaitForPrototypeAjaxRequests = function(timeout) {
    return Selenium.decorateFunctionWithTimeout(function() {
        return selenium.browserbot.getUserWindow().Ajax.activeRequestCount == 0;
    }, timeout);
};

/**
 * Waits for all active Dojo AJAX requests to finish.
 *
 * @param timeout Timeout in milliseconds.
 * @throws SeleniumError If timeout is reached.
 */
Selenium.prototype.doWaitForDojoAjaxRequests = function(timeout) {
    return Selenium.decorateFunctionWithTimeout(function() {
        return selenium.browserbot.getUserWindow().dojo.io.XMLHTTPTransport.inFlight.length == 0;
    }, timeout);
};
