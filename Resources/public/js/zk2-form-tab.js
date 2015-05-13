/* tab */
    var ZkTab = {
        init: function ( tabSel ) {
            this.tabs( tabSel );
        },
        tabs: function ( tabSel ) {
            var tabId;
            var tabSelector = '#' + tabSel;
            if('undefined' != typeof window['localStorage']) {
                if ( localStorage.getItem('id_' + tabSelector) ) {
                    tabId = localStorage.getItem('id_' + tabSelector);
                    if ( 'tabajax' == $('#'+tabId).data('toggle') ) {
                        var loadurl = $('#'+tabId).attr('href');
                        var targ = $('#'+tabId).attr('data-target');
                        $.get(loadurl, function(data) {
                            $(targ).html(data);
                        });
                        $('#'+tabId).tab('show');
                    } else {
                        $('#'+tabId).tab('show');
                    }
                } else {
                    $(tabSelector+' a:first').tab('show');
                }
                $(tabSelector+' a').on('click', function (e) {
                    e.preventDefault();
                    if ( 'tabajax' == $(this).data('toggle') ) {
                        var loadurl = $(this).attr('href');
                        var targ = $(this).attr('data-target');
                        $.get(loadurl, function(data) {
                            $(targ).html(data);
                        });
                        $(this).tab('show');
                    } else {
                        $(this).tab('show');
                    }
                    localStorage.setItem('id_' + tabSelector, e.currentTarget.id);
                });
            } else {
                $(tabSelector+' a:first').tab('show');
                $(tabSelector+' a').on('click', function (e) {
                    e.preventDefault();
                    if ( 'tabajax' == $(this).data('toggle') ) {
                        var loadurl = $(this).attr('href');
                        var targ = $(this).attr('data-target');
                        $.get(loadurl, function(data) {
                            $(targ).html(data);
                        });
                        $(this).tab('show');
                    } else {
                        $(this).tab('show');
                    }
                });
            }
        }
    };
    $(function(){ ZkTab.init('zkTab'); });