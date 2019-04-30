(function () {

    'use strict';

    Thrift.TokenizedTransport = function (url, options, apiKey, token) {
        this.url = url;
        this.wpos = 0;
        this.rpos = 0;
        this.useCORS = (options && options.useCORS);
        this.send_buf = '';
        this.recv_buf = '';
        this.apiKey = apiKey;
        this.token = token;
    };

    Thrift.inherits(Thrift.TokenizedTransport, Thrift.TXHRTransport, "tokenizedTransport");

    /**
     * Modified from  the generated Thrift code to allow us to add an auth token to requests.
     *
     * Sends the current XRH request if the transport was created with a URL
     * and the async parameter is false. If the transport was not created with
     * a URL, or the async parameter is True and no callback is provided, or
     * the URL is an empty string, the current send buffer is returned.
     * @param {object} async - If true the current send buffer is returned.
     * @param {object} callback - Optional async completion callback
     * @returns {undefined|string} Nothing or the current send buffer.
     * @throws {string} If XHR fails.
     */
    Thrift.TokenizedTransport.prototype.flush = function (async, callback) {
        var self = this;
        if ((async && !callback) || this.url === undefined || this.url === '') {
            return this.send_buf;
        }

        var xreq = this.getXmlHttpRequestObject();
        xreq.async = true;

        if (xreq.overrideMimeType) {
            xreq.overrideMimeType('application/json');
        }

        if (callback) {
            //Ignore XHR callbacks until the data arrives, then call the
            //  client's callback
            xreq.onreadystatechange =
                (function() {
                    var clientCallback = callback;
                    return function() {
                        if (this.readyState == 4 && this.status == 200) {
                            self.setRecvBuffer(this.responseText);
                            clientCallback();
                        }
                    };
                }());

            xreq.onerror =
                (function() {
                    var clientCallback = callback;
                    return function() {
                        clientCallback();
                    };
                }());
        }

        xreq.open('POST', this.url, !!async);

        this.tokenizeRequest(xreq);

        if (xreq.setRequestHeader) {
            xreq.setRequestHeader('Accept', 'application/vnd.apache.thrift.json; charset=utf-8');
            xreq.setRequestHeader('Content-Type', 'application/vnd.apache.thrift.json; charset=utf-8');
        }

        xreq.send(this.send_buf);
        if (async && callback) {
            return;
        }

        if (xreq.readyState != 4) {
            throw 'encountered an unknown ajax ready state: ' + xreq.readyState;
        }

        if (xreq.status != 200) {
            throw 'encountered a unknown request status: ' + xreq.status;
        }

        this.recv_buf = xreq.responseText;
        this.recv_buf_sz = this.recv_buf.length;
        this.wpos = this.recv_buf.length;
        this.rpos = 0;
    };

    /**
     * Add our token to the XHR request.
     * @param xreq
     */
    Thrift.TokenizedTransport.prototype.tokenizeRequest = function (xreq) {
        var requestDate = Date.create().toISOString(),
            authHeader;
        xreq.setRequestHeader('X-Thrift-Auth-Mode', 'Token');
        xreq.setRequestHeader('Request-Date', requestDate);

        authHeader = 'Bearer ' + this.token;

        xreq.setRequestHeader('X-Thrift-Auth', authHeader);
    };

})();
