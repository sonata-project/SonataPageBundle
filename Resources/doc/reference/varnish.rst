Varnish
=======

Basic configuration
-------------------
When a user is logged-in as an editor, a ``sonata_page_is_editor`` cookie is set.
So you can configure a Varnish as follows.

VCL for Varnish 4.0::

    vcl 4.0;
    
    backend default {
        .host = "127.0.0.1";
        .port = "8080";
        .connect_timeout = 600s;
        .first_byte_timeout = 600s;
        .between_bytes_timeout = 600s;
        .max_connections = 250;
    }
    
    #
    # RECEIVE REQUEST FROM THE CLIENT
    #
    sub vcl_recv {
    
        unset req.http.X-Forwarded-For;
        set req.http.X-Forwarded-For = client.ip;
    
        # Force lookup if the request is a no-cache request from the client
        if (req.http.Cache-Control ~ "no-cache") {
            return (pass);
        }
    
        if (req.http.Cookie) {
            # removes all cookies named __utm? (utma, utmb...) - tracking thing
            set req.http.Cookie = regsuball(req.http.Cookie, "(^|; ) *__utm.=[^;]+;? *", "\1");
    
            if (req.http.Cookie == "") {
                unset req.http.Cookie;
            }
        }
    
        ## Default request checks
        if (req.method != "GET" &&
            req.method != "HEAD" &&
            req.method != "PUT" &&
            req.method != "POST" &&
            req.method != "TRACE" &&
            req.method != "OPTIONS" &&
            req.method != "DELETE") {
                # Non-RFC2616 or CONNECT which is weird.
                return (pipe);
        }
    
        if (req.method != "GET" && req.method != "HEAD") {
            # We only deal with GET and HEAD by default
            return (pass);
        }
    
        ## Modified from default to allow caching if cookies are set, but not http auth
        if (req.http.Authorization) {
            /* Not cacheable by default */
            return (pass);
        }
    
        # Don't cache user/application area
        if (req.url ~ "(^/app.php|^/app_dev.php|^)/([a-z]{2})/(payment|order|booking|media|autocomplete|monitor).*") {
            return (pass);
        }
    
        # Don't cache admin area
        if (req.url ~ "(^/app.php|^/app_dev.php|^)/admin" || req.url ~ "(^/app.php|^/app_dev.php|^)/(([a-z]{2})/admin)") {
            return (pass);
        }
    
        # Don't cache security area
        if (req.url ~ "(^/app.php|^/app_dev.php|^)/(([a-z]{2}/|)(login|logout|login_check).*)") {
            return (pass);
        }
    
        ## Don't cache editor logged-in user sessions
        if (req.http.Cookie ~ "(sonata_page_is_editor)") {
            return (pass);
        }
    
        return (hash);
    }
    
    #
    # RECEIVE RESPONSE FROM THE APPLICATION
    #
    sub vcl_backend_response
    {
        # These status codes should always pass through and never cache.
        if (beresp.status == 404) {
            set beresp.http.X-Cache-Rule = "YES: but for 1m - beresp.status : " + beresp.status;
            set beresp.ttl = 1m;
    
            return (deliver);
        }
    
        if (beresp.status == 503 || beresp.status == 500) {
            set beresp.http.X-Cache-Rule = "NOT: beresp.status : " + beresp.status;
            set beresp.ttl = 0s;
            set beresp.uncacheable = true;
    
            return (deliver);
        }
    
        # Force the cache for the home
        if (bereq.url ~ "(^/app.php|^/app_dev.php|^)/([a-z]{2})(|/)$") {
            set beresp.ttl = 1m;
        }
    
        if (bereq.url ~ "\.(jpg|jpeg|gif|png|ico|css|zip|tgz|gz|rar|bz2|pdf|txt|tar|wav|bmp|rtf|js|flv|swf|html|htm|mov|avi|mp3|mpg)$") {
            unset beresp.http.set-cookie;
            set beresp.http.X-Cache-Rule = "YES: static files";
            set beresp.ttl = 24h;
        }
    
        #if (obj.http.Set-Cookie) {
        #    set obj.http.X-Cache-Rule = "NO: !obj.Set-Cookie";
        #    return (hit_for_pass);
        #}
    
        # No cache for Sonata Editor
        if (bereq.http.Cookie ~ "sonata_page_is_editor") {
            set beresp.ttl = 0s;
            set beresp.http.X-Cache-Rule = "NO: user has ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT";
        }
    
        if (!beresp.ttl > 0s) {
            set beresp.http.X-Cache-Rule = "NO: beresp.ttl == 0";
            set beresp.uncacheable = true;
            return (deliver);
        }
    
        # All tests passed, therefore item is cacheable
        set beresp.http.X-Cache-Rule = "YES with ttl: " + beresp.ttl;
    
        # remove cookies for cached response
        unset beresp.http.set-cookie;
    
        return (deliver);
    }
    
    sub vcl_deliver {
        # add cache hit data
        if (obj.hits > 0) {
            # if hit add hit count
            set resp.http.X-Cache = "HIT";
            set resp.http.X-Cache-Hits = obj.hits;
        } else {
            set resp.http.X-Cache = "MISS";
        }
    }

VCL for Varnish 3.0::

    backend default {
        .host = "127.0.0.1";
        .port = "8080";
        .connect_timeout = 600s;
        .first_byte_timeout = 600s;
        .between_bytes_timeout = 600s;
        .max_connections = 250;
    }

    #
    # RECEIVE REQUEST FROM THE CLIENT
    #
    sub vcl_recv {

        # Allow a grace period for offering "stale" data in case backend lags
        #set req.grace = 60s;
        set req.grace = 5m;

        remove req.http.X-Forwarded-For;
        set req.http.X-Forwarded-For = client.ip;

        # Force lookup if the request is a no-cache request from the client
        if (req.http.Cache-Control ~ "no-cache") {
            return (pass);
        }

        if (req.http.Cookie) {
            # removes all cookies named __utm? (utma, utmb...) - tracking thing
            set req.http.Cookie = regsuball(req.http.Cookie, "(^|; ) *__utm.=[^;]+;? *", "\1");

            if (req.http.Cookie == "") {
                remove req.http.Cookie;
            }
        }

        ## Default request checks
        if (req.request != "GET" &&
            req.request != "HEAD" &&
            req.request != "PUT" &&
            req.request != "POST" &&
            req.request != "TRACE" &&
            req.request != "OPTIONS" &&
            req.request != "DELETE") {
                # Non-RFC2616 or CONNECT which is weird.
                return (pipe);
        }

        if (req.request != "GET" && req.request != "HEAD") {
            # We only deal with GET and HEAD by default
            return (pass);
        }

        ## Modified from default to allow caching if cookies are set, but not http auth
        if (req.http.Authorization) {
            /* Not cacheable by default */
            return (pass);
        }

        # Don't cache user/application area
        if (req.url ~ "(^/app.php|^/app_dev.php|^)/([a-z]{2})/(payment|order|booking|media|autocomplete|monitor).*") {
            return (pass);
        }

        # Don't cache admin area
        if (req.url ~ "(^/app.php|^/app_dev.php|^)/admin" || req.url ~ "(^/app.php|^/app_dev.php|^)/(([a-z]{2})/admin)") {
            return (pass);
        }

        # Don't cache security area
        if (req.url ~ "(^/app.php|^/app_dev.php|^)/(([a-z]{2}/|)(login|logout|login_check).*)") {
            return (pass);
        }

        ## Don't cache editor logged-in user sessions
        if (req.http.Cookie ~ "(sonata_page_is_editor)") {
            return (pass);
        }

        return (lookup);
    }

    #
    # RECEIVE RESPONSE FROM THE APPLICATION
    #
    sub vcl_fetch
    {
        # These status codes should always pass through and never cache.
        if (beresp.status == 404) {
            set beresp.http.X-Cache-Rule = "YES: but for 1m - beresp.status : " + beresp.status;
            set beresp.ttl = 1m;

            return (deliver);
        }

        if (beresp.status == 503 || beresp.status == 500) {
            set beresp.http.X-Cache-Rule = "NOT: beresp.status : " + beresp.status;
            set beresp.ttl = 0s;

            return (hit_for_pass);
        }

        # Force the cache for the home
        if (req.url ~ "(^/app.php|^/app_dev.php|^)/([a-z]{2})(|/)$") {
            set beresp.ttl = 1m;
        }

        if (req.url ~ "\.(jpg|jpeg|gif|png|ico|css|zip|tgz|gz|rar|bz2|pdf|txt|tar|wav|bmp|rtf|js|flv|swf|html|htm|mov|avi|mp3|mpg)$") {
            unset beresp.http.set-cookie;
            set beresp.http.X-Cache-Rule = "YES: static files";
            set beresp.ttl = 24h;
        }

        #if (obj.http.Set-Cookie) {
        #    set obj.http.X-Cache-Rule = "NO: !obj.Set-Cookie";
        #    return (hit_for_pass);
        #}

        # No cache for Sonata Editor
        if (req.http.Cookie ~ "sonata_page_is_editor") {
            set beresp.ttl = 0s;
            set beresp.http.X-Cache-Rule = "NO: user has ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT";
        }

        if (!beresp.ttl > 0s) {
            set beresp.http.X-Cache-Rule = "NO: beresp.ttl == 0";

            return (hit_for_pass);
        }

        # All tests passed, therefore item is cacheable
        set beresp.http.X-Cache-Rule = "YES with ttl: " + beresp.ttl;

        # remove cookies for cached response
        unset beresp.http.set-cookie;

        return (deliver);
    }

    sub vcl_deliver {
        # add cache hit data
        if (obj.hits > 0) {
            # if hit add hit count
            set resp.http.X-Cache = "HIT";
            set resp.http.X-Cache-Hits = obj.hits;
        } else {
            set resp.http.X-Cache = "MISS";
        }
    }

VCL for varnish 2.1::

    backend default {
        .host = "127.0.0.1";
        .port = "8080";
        .connect_timeout = 600s;
        .first_byte_timeout = 600s;
        .between_bytes_timeout = 600s;
        .max_connections = 250;
    }

    #
    # RECEIVE REQUEST FROM THE CLIENT
    #
    sub vcl_recv {

        # Allow a grace period for offering "stale" data in case backend lags
        #set req.grace = 60s;
        set req.grace = 5m;

        remove req.http.X-Forwarded-For;
        set req.http.X-Forwarded-For = client.ip;

        # Force lookup if the request is a no-cache request from the client
        if (req.http.Cache-Control ~ "no-cache") {
            return (pass);
        }

        if (req.http.Cookie) {
            # removes all cookies named __utm? (utma, utmb...) - tracking thing
            set req.http.Cookie = regsuball(req.http.Cookie, "(^|; ) *__utm.=[^;]+;? *", "\1");

            if (req.http.Cookie == "") {
                remove req.http.Cookie;
            }
        }

        ## Default request checks
        if (req.request != "GET" &&
            req.request != "HEAD" &&
            req.request != "PUT" &&
            req.request != "POST" &&
            req.request != "TRACE" &&
            req.request != "OPTIONS" &&
            req.request != "DELETE") {
                # Non-RFC2616 or CONNECT which is weird.
                return (pipe);
        }

        if (req.request != "GET" && req.request != "HEAD") {
            # We only deal with GET and HEAD by default
            return (pass);
        }

        ## Modified from default to allow caching if cookies are set, but not http auth
        if (req.http.Authorization) {
            /* Not cacheable by default */
            return (pass);
        }

        # Don't cache user/application area
        if (req.url ~ "(^/app.php|^/app_dev.php|^)/([a-z]{2})/(payment|order|booking|media|autocomplete|monitor).*") {
            return (pass);
        }

        # Don't cache callcenter
        if (req.url ~ "(^/app.php|^/app_dev.php|^)/callcenter") {
            return (pass);
        }

        # Don't cache admin area
        if (req.url ~ "(^/app.php|^/app_dev.php|^)/admin" || req.url ~ "(^/app.php|^/app_dev.php|^)/(([a-z]{2})/admin)") {
            return (pass);
        }

        # Don't cache security area
        if (req.url ~ "(^/app.php|^/app_dev.php|^)/(([a-z]{2}/|)(login|logout|login_check).*)") {
            return (pass);
        }

        ## Don't cache editor logged-in user sessions
        if (req.http.Cookie ~ "(sonata_page_is_editor)") {
            return (pass);
        }

        return (lookup);
    }

    #
    # RECEIVE RESPONSE FROM THE APPLICATION
    #
    sub vcl_fetch
    {
        # These status codes should always pass through and never cache.
        if (beresp.status == 404) {
            set beresp.http.X-Cache-Rule = "YES: but for 1m - beresp.status : "  beresp.status;
            set beresp.ttl = 1m;

            return (deliver);
        }

        if (beresp.status == 503 || beresp.status == 500) {
            set beresp.http.X-Cache-Rule = "NOT: beresp.status : " beresp.status;
            set beresp.ttl = 0s;

            return (pass);
        }

        # Force the cache for the home
        if (req.url ~ "(^/app.php|^/app_dev.php|^)/([a-z]{2})(|/)$") {
            set beresp.ttl = 1m;
        }

        if (req.url ~ "\.(jpg|jpeg|gif|png|ico|css|zip|tgz|gz|rar|bz2|pdf|txt|tar|wav|bmp|rtf|js|flv|swf|html|htm|mov|avi|mp3|mpg)$") {
            unset beresp.http.set-cookie;
            set beresp.http.X-Cache-Rule = "YES: static files";
            set beresp.ttl = 24h;
        }

        #if (obj.http.Set-Cookie) {
        #    set obj.http.X-Cache-Rule = "NO: !obj.Set-Cookie";
        #    return (hit_for_pass);
        #}

        # No cache for Sonata Editor
        if (req.http.Cookie ~ "sonata_page_is_editor") {
            set beresp.ttl = 0s;
            set beresp.http.X-Cache-Rule = "NO: user has ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT";
        }

        if (!beresp.cacheable) {
            set beresp.http.X-Cache-Rule = "NO: beresp.ttl == 0";

            return (pass);
        }

        # All tests passed, therefore item is cacheable
        set beresp.http.X-Cache-Rule = "YES with ttl: "  beresp.ttl;

        # remove cookies for cached response
        unset beresp.http.set-cookie;

        return (deliver);
    }

    sub vcl_deliver {
        # add cache hit data
        if (obj.hits > 0) {
            # if hit add hit count
            set resp.http.X-Cache = "HIT";
            set resp.http.X-Cache-Hits = obj.hits;
        } else {
            set resp.http.X-Cache = "MISS";
        }
    }

Using ESI
---------
Using Edge Side Includes ? Modify your configuration to `advertise ESI support,
enable ESI parsing <http://http://symfony.com/doc/current/cookbook/cache/varnish.html#configuration>`_
and disable cookies when (and if) relevant (it's not relevant if you are caching
even when there are cookies, like above).

Varnish 3.0::

    sub vcl_recv {
        // ...
        // Add a Surrogate-Capability header to announce ESI support.
        set req.http.Surrogate-Capability = "varnish_your_host=ESI/1.0";

        // This part is not useful if you are caching even when there are cookies,
        // like above.
        if (req.url ~ "^/sonata/page/cache/esi/") {
            // Let's assume your caching blocks that don't need the session
            unset req.http.Cookie;
        }
        // ...
    }

    sub vcl_fetch {
        // ...
        /*
        Check for ESI acknowledgement
        and remove Surrogate-Control header
        */
        if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
            unset beresp.http.Surrogate-Control;

            set beresp.do_esi = true;
        }
        // This part is not useful if you are caching even when there are cookies,
        // like above.
        if (req.url ~ "^/sonata/page/cache/esi/") {
            // Same assumption here, choose wisely which blocks will be cached.
            unset beresp.http.Set-Cookie;
        }
        // ...
    }

