var selectedAnchorClassName = 'selected_tree_node';

// This function is tailored for 0-FS-KB_CAT
function addCategoryTreeClickHandler() {
    $("#categorytree ul li a").click(function () {

        // expand selected category node
        var doPropagate = treeNodeClick($(this).parent());

        var iconSpan = $(this).children().first();

        if (!doPropagate) {
            $("#resultPanel").hide();
            $("#listResult_tree").hide();
            return;
        }

        $("#resultPanel").show();
        $("#listResult_tree").show();

        var selectBox = $(".searchable")[0];
        var related_result = $(this).attr("value");
        var name = $(this).attr("name");
        var searchword = name;
        if (searchword == "" || searchword == undefined || searchword == null) {
            searchword = "main";
        }
        document.title = "KATEGORI " + searchword;
        //_paq.push(['trackPageView']);
        //_paq.push(['enableLinkTracking']);
        //_paq.push(['setDocumentTitle', document.title]);

        $("#Titel_kat").html("Artikler i kategorien: " + name);

        var portalsId = returnValuefromForm($(".checkbox"), "ck");
        $("#listResult_tree").empty();

        if (portalsId != undefined && related_result.length > 0) {
            var data = {
                SelectedKat: related_result,
                PortalsId: portalsId
            }
            var detail = $(this).attr("section");

            var baseurl = getBaseUrl(window.location.href);

            var postUrl = baseurl + "/Category/getCategoryList";
            var detail = $(this).attr("section");
            $.ajax({
                url: baseurl + "/Category/getCategoryList",
                type: "POST",
                dataType: 'json',
                contentType: 'application/json; charset=utf-8',
                data: JSON.stringify(data, null, 2),
                success: function (data) {
                    var obj = jQuery.parseJSON(data.data);
                    var stringResult = "";
                    jQuery.each(obj, function (i, val) {

                        //listResult
                        stringResult += "<div class='table-responsive'>";
                        stringResult += "<table class='table'>";

                        jQuery.each(val, function (j, art) {
                            stringResult += "<tr class='tr_res_header'>";
                            stringResult += "<td class='active'><a href=" + baseurl + "/Article/" + art.Customer_id + "/" + art.Portal_Id + "/" + art.ArticleId + ">" + art.ArticleTitle + "</a></td>";
                            stringResult += "</tr><tr class='tr_res'>";
                            stringResult += "<td class='active'>" + art.Description + "</td>";
                            stringResult += "</tr>";
                        });

                        stringResult += "</table></div>";
                    });
                    $("#listResult_tree").append(stringResult);
                },
                error: function (data) {
                }
            });
        } else {
            alert("Vælg mindst én Kategori")
        }
    });
}

function initCategoryTree(selectedTreeNode) {
    hideTreeViewRootNode()
    collapseTree();
}

function treeNodeClick(treeNodeLi) {
    try {
        if (isTreeNodeSelected(treeNodeLi)) {
            collapseTree();
            dispatchTreeviewCollapsedEvent();
            return false;
        }
        else {
            collapseTree();
            selectSingleTreeNode(treeNodeLi);
            return true;
        }
    } catch (e) {
        // Intentionally blank - do nothing
    }
    return true;
}

function getTreeNodeByValue(treeNodeValue) {
    try {
        return $('#categorytree li a').first(value = treeNodeValue);
    } catch (e) {
        return null;
    }
}

function hideTreeViewRootNode() {
    try {
        var rootNode = getTreeNodeByValue(1)
        rootNode.hide();
        rootNode.prev().hide();
    } catch (e) {
        // no such node...do nothing
    }
}

function unselectAllTreeNodes() {
    try {
        $("#categorytree ul > li > a").each(function () {
            $(this).removeClass(selectedAnchorClassName);
        });
    } catch (e) {
    }
}

function collapseTree() {
    unselectAllTreeNodes();
    $('#categorytree > li > ul > li > ul').each(
        function () {
            $(this).hide();
        }
    );
    $('#categorytree span.minus').each(
        function () {
            if (!$(this).hasClass('nochildren'))
                $(this).removeClass('minus').addClass('plus');
        }
    );
}

function selectTreeNode(treeNodeLi) {
    var currLiNode = treeNodeLi;
    selectSingleTreeNode(currLiNode);
}

function selectSingleTreeNode(treeNodeLi) {
    try {
        var iconSpan = treeNodeLi.find('span').first();
        if (!iconSpan.hasClass('nochildren')) {
            iconSpan.removeClass('plus').addClass('minus');
            iconSpan.closest('li.head').children('span').first().removeClass('plus').addClass('minus');
        }
        var childAnchor = treeNodeLi.find('a').first();
        childAnchor.addClass(selectedAnchorClassName);
        expandParents(treeNodeLi);
        var childUl = treeNodeLi.find('ul').first();
        childUl.fadeIn(100);
    } catch (e) {
        // Intentionally blank - do nothing
    }
}

function expandParents(treeNodeLi) {
    try {
        if (hasParentUl(treeNodeLi))
        {
            var parentUl = treeNodeLi.closest('ul.head', $('#categorytree'));
            parentUl.show();
            if (parentUl.parent().parent().parent().parent() != $('ul#categorytree'))
                parentUl.prev().children().first().removeClass('plus').addClass('minus');
        }
    } catch (e) {

    }
}

function isTreeNodeSelected(treeNodeLi) {
    var anchor = treeNodeLi.find('a').first();
    if (typeof anchor != 'undefined') {
        if (anchor.hasClass(selectedAnchorClassName)) {
            return true;
        }
    }
    return false;
}

function hasParentUl(object) {
    return (object.closest('ul.head', $('#categorytree')).length > 0);
}

function dispatchTreeviewCollapsedEvent() {
    $('#categorytree').trigger('treeviewcollapsed', [null]);
}

