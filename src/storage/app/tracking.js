(function() {

    function Cookie() {};

    Cookie.setCookie = function(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
        return value;
    };

    Cookie.getCookie = function(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length == 2) {
            return parts.pop().split(";").shift();
        }
    };

    Cookie.deleteCookie = function(name) {
        document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    };

    function Visitor(vid, visitsCount) {
        var _visitor = this;

        _visitor.vid = vid ? vid : generateUuidV4();
        _visitor.visitsCount = visitsCount ? visitsCount : 1;
        _visitor.agent = navigator.userAgent;
        _visitor.timestamp = new Date();
        _visitor.url = window.location.href;

        function generateUuidV4() {
            return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
            (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
            )
        }
    }

    function fetchPublicId() {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var data =  JSON.parse(this.responseText);
                visitor.ipAddress = data.ip;
                postTrackingData();
            }
        };

        xhttp.open("GET", "https://api.ipify.org?format=json");
        xhttp.send();
    };

    function postTrackingData() {
        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", stalkerUrl);
        xhttp.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
        xhttp.send(JSON.stringify(visitor));
    }

    var visitorId = Cookie.getCookie('vid');

    if (!visitorId) {
        var visitor = new Visitor();
        Cookie.setCookie('vid', visitor.vid);
        Cookie.setCookie('visits_count', visitor.visitsCount);
    } else {
        var visitsCount = parseInt(Cookie.getCookie("visits_count"));
        visitsCount++;
        var visitor = new Visitor(visitorId, visitsCount);
        Cookie.setCookie('visits_count', visitsCount);
    }

    fetchPublicId();

}())
