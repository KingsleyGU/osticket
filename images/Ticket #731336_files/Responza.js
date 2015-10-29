/* Responza.js */
/* contains all utility scripts for Responza */



        // Save search term
        function saveSearchTerm(customerId, customerName, pageId, baseUrl, searchTerm, portalId) {
            var data = {
                search_term: searchTerm,
                portal_id: portalId
            }
            var postUrl = baseUrl + "/api/SearchPortal/SaveSearchPortal/?portal_id=" + portalId + "&search_term=" + searchTerm;
            $.ajax({
                url: postUrl,
                type: "POST",
                dataType: 'json',
                contentType: 'application/json; charset=utf-8',
                data: data,
                success: function (data) {
                    var search_term = searchTerm;

                    var customer_id = customerId;
                    var page_id = pageId;
                    var portal_id = portalId;
                    var customer_name = customerName;
                    var postUrl = "";
                    window.location.href = "";
                    window.location.href = baseUrl + "/contentkb/" + customerId + "_" + pageId + "/" + customerId + "/" + portalId + "/Search?customer=" + customerName + "&title=" + customerId + "_" + pageId + "&page=1&sparam=" + searchTerm;
                },
                error: function (data) {
                }
            });
        }

        function getSearchPortal(portalId, baseUrl) {
            var data = {
                portal_id: portalId
            }

            var url = window.location.href.toLowerCase();
            var postUrl = baseUrl + "/api/SearchPortal/getsearchPortal?portal_id=" + portalId;
            $.ajax({
                url: postUrl,
                type: "POST",
                dataType: 'json',
                contentType: 'application/json; charset=utf-8',
                data: data,
                success: function (data) {
                    var result = jQuery.parseJSON(data);
                    var hits = [];

                    if (result.message == "None") {
                        hits["no-suggestion"];
                    } else {
                        $.each(result.hits, function (i, item) {
                            hits.push(item);
                        });
                    }
                    $("#SearchText").autocomplete({
                        source: hits
                    });
                },
                error: function (data) {
                }
            });
        }

        /* Hit counter */
        var cookieName = "responzahits";

        function getMidnightToday() {
            var today = new Date(new Date().setHours(24, 0, 0, 0));
            return today.toString();
        }

        function createCookie(valueArray) {
            var expires = getMidnightToday();
            document.cookie = cookieName + "=" + new Array(valueArray) + "; expires=" + expires + "; path=/";
        }

        function readCookieArticleIds() {
            var nameEQ = escape(cookieName) + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i].trim();
                if (c.indexOf('responzahits') == 0) {
                    while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                    // If no cookie or ArticleId not in cookie...
                    if (c.indexOf(nameEQ) === 0 && c.substring(nameEQ.length, 9) != 'undefined' && c.substring(nameEQ.length, c.length) != "")
                        return c.substring(nameEQ.length, c.length).split(',');
                }
            }
            return null;
        }

        function eraseCookie() {
            createCookie(cookieName, "", -1);
        }

        function updateCookie(articleId) {
            var storedArticleIds = readCookieArticleIds();
            if (storedArticleIds == null || typeof storedArticleIds == 'undefined') {
                createCookie(new Array([articleId]));
            }
            else {
                var articleIds = (storedArticleIds.indexOf(',') > -1) ? new Array(storedArticleIds.split(',')) : new Array(storedArticleIds);
                if (articleId != '' && $.inArray(articleId, new Array([articleIds])) == -1) {
                    articleIds.push(articleId);
                    createCookie(new Array([articleIds]));
                }
            }
        }

        // Updates hit counter server-side &, if successful, updates cookie
        function incrementArticleHitCounter(articleItemId) {
            var model = {
                ArticleItemId: articleItemId
            }
            var postUrl = window.location.href.split("/Article")[0] + "/api/ArticleHitCount";
            var detail = $(this).attr("section");

            $.ajax({
                url: window.location.href.split("/Article")[0] + "/api/ArticleHitCount",
                type: "POST",
                dataType: 'json',
                contentType: 'application/json; charset=utf-8',
                data: JSON.stringify(model),
                success: function (data) {
                    updateCookie(articleItemId);
                },
                error: function (data) {
                }
            });
        }

        function handleArticleHitCount(articleItemId) {
            var hitArticleIds = readCookieArticleIds(cookieName);

            // If Article wasn't read...
            if (hitArticleIds == null || $.inArray(String(articleItemId), hitArticleIds) == -1) {
                incrementArticleHitCounter(articleItemId);
            }
        }

         // Returns base url w '/' appended,
        // when given a full fqdn
        function getBaseUrl(url) {
            pathArray = location.href.split('/');
            protocol = pathArray[0];
            host = pathArray[2];
            var baseUrl = protocol + '//' + host + '/';
            return baseUrl;
        }

        //Fix for HTML encoding on search persist
        function htmlEncode(value) {
            //create a in-memory div, set it's inner text(which jQuery automatically encodes)
            //then grab the encoded contents back out.  The div never exists on the page.
            return $('<div/>').text(value).html();
        }

        function htmlDecode(value) {
            return $('<div/>').html(value).text();
        }

        /* Utility Functions */

        function decodeHtmlNumeric(str) {
            return str.replace(/&#([0-9]{1,7});/g, function (g, m1) {
                return String.fromCharCode(parseInt(m1, 10));
            }).replace(/&#[xX]([0-9a-fA-F]{1,6});/g, function (g, m1) {
                return String.fromCharCode(parseInt(m1, 16));
            });
        }

        function isEmailValid(email) {
            var emailReg = new RegExp(/^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i);
            return emailReg.test(email);
        }

