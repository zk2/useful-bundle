(function ($) {

    $.fn.ZkMultiSelectWidget = function (settings) {

        settings = $.extend({}, $.fn.ZkMultiSelectWidget.defaults, settings);

        var originalSelect = $(this).hide();
        var isDisabled = originalSelect.is(':disabled');
        var originalId = originalSelect.attr('id');
        var optionsSelect = originalSelect.find('option');

        var generalDiv = $('<div/>')
                .addClass('zk-general-div')
                .attr('id', 'zk-general-div__' + originalId)
                .css({
                    'width': settings.ZkWidth * 2 + 50 + (settings.ZkRange ? 50 : 0)
                })
            ;

        var descrDiv = $('<div/>')
                .addClass('zk-descr-div')
                .css({
                    'width': settings.ZkWidth * 2 + 40 + (settings.ZkRange ? 40 : 0)
                })
            ;

        var headerDiv = $('<div/>')
                .addClass('zk-header-div')
            ;

        var leftHeader = $('<div/>')
                .addClass('zk-header')
                .addClass('zk-header-left')
                .css({
                    'width': settings.ZkWidth
                })
            ;

        var rightHeader = $('<div/>')
                .addClass('zk-header')
                .addClass('zk-header-right')
                .css({
                    'margin-left': '40px',
                    'width': 1 * settings.ZkWidth - 10,
                    'color': 'blue'
                })
                .text('Selected')
            ;

        var mainDiv = $('<div/>')
                .addClass('zk-main-div')
            ;

        var leftDiv = $('<div/>')
                .addClass('zk-child-div')
                .addClass('zk-child-div-left')
                .css({
                    'width': settings.ZkWidth,
                    'height': settings.ZkHeight,
                    'border': '1px solid #665',
                    'background': isDisabled ? '#FBFBFB' : '#FFFFFF'
                })
            ;

        var rightDiv = $('<div/>')
                .addClass('zk-child-div')
                .addClass('zk-child-div-right')
                .css({
                    'width': settings.ZkWidth,
                    'height': settings.ZkHeight,
                    'border': '1px solid #665',
                    'background': isDisabled ? '#FBFBFB' : '#FFFFFF'
                })
            ;

        var funcDiv = $('<div/>')
                .addClass('zk-child-div')
                .addClass('zk-func-div')
                .css({
                    'padding-top': settings.ZkHeight / 2 - 32
                })
            ;

        var rangeDiv = $('<div/>')
                .addClass('zk-child-div')
                .addClass('zk-range-div')
                .css({
                    'padding-top': settings.ZkHeight / 2 - 32
                })
            ;

        var leftUl = $('<ul/>')
                .attr('id', originalId + '__left')
            ;

        var rightUl = $('<ul/>')
                .attr('id', originalId + '__right')
            ;

        var rightAdd = $('<p/>')
                .addClass('zk-p')
                .addClass('zk-right-p')
                .addClass('zk-noact-p')
                .attr('title', 'Add selected')
                .html('&rsaquo;')
            ;

        var leftAdd = $('<p/>')
                .addClass('zk-p')
                .addClass('zk-left-p')
                .addClass('zk-noact-p')
                .attr('title', 'Remove selected')
                .html('&lsaquo;')
            ;

        var topAdd = $('<p/>')
                .addClass('zk-range-p')
                .addClass('zk-top-p')
                .addClass('zk-noact-p')
                .attr('title', 'Top')
                .html('&#8593;')
            ;

        var bottomAdd = $('<p/>')
                .addClass('zk-range-p')
                .addClass('zk-bottom-p')
                .addClass('zk-noact-p')
                .attr('title', 'Bottom')
                .html('&#8595;')
            ;

        var inputSearch = $('<input class="zk-search-input" />').hide();
        var imageSearch = $('<img alt="Search" title="Search" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAABSlBMVEUAAACOh6qCfZiOia1hYXt2dpB1dYvFxdzOzuJbW1+Ii6tydIu6vNgAAACBhZ+Kk7mzveC2wOCgs9yBlLeesdKktNKGmryOqM6euOCjveWmv+Xd5fGMnreTrMymye+WsM6Zssyux+Ckz/Wpz/HC2u+o0vOq3f+54/+s3//q9/+w4/+55v+x5P/X8v+/7P/g7/W87v+87//a7fO98P+/8f/C8f/A8v/G8//F9f/J9f/N9v/o+//C9f/G9v/E9//N+P/G+f/H+f/R/P/R/f/N///W///X///Y///Z///a///b///c///l///n///o///s///3///4///7///U0Mn/uFL/uVP+uFPKsIn/zo7/2KX/473/zIzfiEDZhUPWg0TPjFiJdXOodnOneXmOi4v///+rq6uhoaGWlpaMjIyBgYF3d3dsbGxiYmJXV1crjDT0AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfdBg8VMQqJMs+QAAAArUlEQVQY02NggAB2EXEJoQwGOOCRtrY3MFfih/HZVVI09TS0jFVZoQKCVrrqJjaOdrbCUF1SltqGLm5e7k6y2RABSUs1G1cff18PhVyIgICpjoOXf1CApwxUgEPOzM7dLzDFiAWiJTKYS9nC2ctbny8XbGhEWHwym5i8oig3hB8dFpfElJmWnZubA+ZHhsYmMXOmwx0dFQLiZyF8ER6TiMJnSE1gROEzMGTkovAB5IkiQu1ej7sAAAAASUVORK5CYII=" />')
            .hide();
        var deleteSearch = $('<img alt="Delete" title="Delete" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAADXSURBVHjaYvz//z8DJYCJgUJAsQEsyJydqakgCuYnRjS1cHH32bNxuuC/u6wsAwgjacAnjumFf0ZGDH///mVwkZKCKf4PYoPEQHKEwoBx98aNDP+BCv/8/s3gICoKxiA2SAwkh+41DBcA/ce4d8sWht+/fqFgkBhIDm8gIoNfQE1kRSMwJv4bMDEx/P75k4HNxASMQWyQGEiOkAH/tf/8ATuZ08yM4ejhw2AMYoPEQHLosYDhBW4LCzB9/MQJuJ9BNptBxRnOnEENdeS8QE5CYhz6mQkgwABTp285v4gwGwAAAABJRU5ErkJggg==" />')
            .hide();

        if (settings.ZkSearch) {
            leftHeader.append(
                $('<span/>')
                    .css({'width': settings.ZkWidth})
                    .append(imageSearch.show())
                    .append(inputSearch.css({
                        'margin-left': '5px',
                        'display': 'inline',
                        'background': 'white',
                        'border': '1px solid #999'
                    }).show())
                    .append(deleteSearch)
            );
        }

        // LOAD ITEMS
        optionsSelect.each(function (i, opt) {
            var li = $('<li/>');
            if (settings.ZkOptionsDisabled === true || $.inArray(parseInt(opt.value), settings.ZkOptionsDisabled) != -1) {
                li.addClass('zk-disabled');
            }
            li.data('idx', opt.value);
            li.append(opt.text);
            if (opt.selected) rightUl.append(li);
            else leftUl.append(li);
        });

        // SEARCH LEFT FUNCTION
        var searchV = false;
        var toid = null;
        var ZkSearch = function () {
            if (searchV == inputSearch.val()) return;
            searchV = inputSearch.val();
            setTimeout(function () {
                leftUl.children().hide();
                if (searchV == "") {
                    leftUl.children().show();
                    deleteSearch.hide();
                } else {
                    leftUl.children().each(function () {
                        var myText = $(this).text();
                        var find = myText.toUpperCase().indexOf(searchV.toUpperCase());
                        if (find != -1) {
                            $(this).show();
                        }
                    });
                    if (leftUl.children().filter(':visible').length == 0) {
                        inputSearch.css({'border': '1px red solid'});
                    }
                    deleteSearch.css({'cursor': 'pointer', 'display': 'inline'}).show();
                }
            }, 5);
        };

        // REMOVE FILTER ON SEARCH FUNCTION
        deleteSearch.click(function (e) {
            e.preventDefault();
            clearTimeout(toid);
            inputSearch.val("").removeAttr("style");
            ZkSearch();
            return false;
        });

        // ON CHANGE TEXT INPUT
        inputSearch.keyup(function () {
            clearTimeout(toid);
            toid = setTimeout(ZkSearch, 200);
        });


        generalDiv.append(headerDiv.append(leftHeader).append(rightHeader));
        leftDiv.append(leftUl);
        rightDiv.append(rightUl);
        funcDiv.append(rightAdd).append(leftAdd);
        mainDiv.append(leftDiv).append(funcDiv).append(rightDiv);
        if (settings.ZkRange) {
            rightHeader.css({'width': 1 * settings.ZkWidth + 40 - 10});
            mainDiv.append(rangeDiv.append(topAdd).append(bottomAdd));
        }
        generalDiv.append(mainDiv);
        if (settings.ZkDescrUrl) {
            generalDiv.append(descrDiv);
        }
        originalSelect.after(generalDiv);

        $('#zk-general-div__' + originalId + ' .zk-main-div .zk-child-div ul li').click(function (e) {
            e.preventDefault();
            if (settings.ZkDescrUrl) {
                triggerDescr($(this).data('idx'));
            }
            var ind = $(this).index();
            triggerClickLi($(this), e.ctrlKey, e.shiftKey, ind);
            triggerLeltRight();
        });

        var triggerLeltRight = function () {
            $('#zk-general-div__' + originalId + ' .zk-main-div .zk-child-div-left ul li').each(function () {
                if ($(this).hasClass('zk-li-act')) {
                    rightAdd.removeClass('zk-noact-p').addClass('zk-act-p');
                    return false;
                }
                rightAdd.removeClass('zk-act-p').addClass('zk-noact-p');
            });
            $('#zk-general-div__' + originalId + ' .zk-main-div .zk-child-div-right ul li').each(function () {
                if ($(this).hasClass('zk-li-act')) {
                    leftAdd.removeClass('zk-noact-p').addClass('zk-act-p');
                    return false;
                }
                leftAdd.removeClass('zk-act-p').addClass('zk-noact-p');
            });
        };

        var triggerClickLi = function (li, ctrl, shift, ind) {
            if (li.hasClass('zk-disabled')) return;
            var thisUl = li.parent();
            var thisAllLi = thisUl.find('li');
            thisAllLi.removeClass('zk-li-act-nofocus');
            li.removeClass('zk-li-act-nofocus');
            if (thisUl.parent().hasClass('zk-child-div-right')) {
                topAdd.removeClass('zk-noact-p').addClass('zk-act-p');
                bottomAdd.removeClass('zk-noact-p').addClass('zk-act-p');
            } else {
                topAdd.removeClass('zk-act-p').addClass('zk-noact-p');
                bottomAdd.removeClass('zk-act-p').addClass('zk-noact-p');
            }
            if (true == ctrl) {
                li.toggleClass('zk-li-act');
            } else if (true == shift) {
                if (ind > -1) {
                    var findOk = false;
                    var xStart = ind;
                    var xFinish = ind;
                    for (var i = ind; i >= 0; i--) {
                        var tempEl = thisAllLi.eq(i);
                        if (tempEl.hasClass('zk-li-act')) {
                            xStart = tempEl.index();
                            findOk = true;
                            i = -1;
                        }
                    }
                    if (!findOk) {
                        var allLenght = thisAllLi.last().index();
                        for (var i = ind; i <= allLenght; i++) {
                            var tempEl = thisAllLi.eq(i);
                            if (tempEl.hasClass('zk-li-act')) {
                                xFinish = tempEl.index();
                                findOk = true;
                                i = allLenght + 1;
                            }
                        }
                    }
                    thisAllLi.slice(xStart, xFinish + 1).each(function () {
                        if ($(this).is(':visible')) $(this).addClass('zk-li-act');
                    });
                }
            } else {
                thisAllLi.removeClass('zk-li-act');
                li.toggleClass('zk-li-act');
            }

            var act = thisUl.parent().hasClass('zk-child-div-left') ? 'left' : 'right';
            var noact = 'left' == act ? 'right' : 'left';
            $('#zk-general-div__' + originalId + ' .zk-main-div .zk-child-div-' + noact + ' ul .zk-li-act')
                .addClass('zk-li-act-nofocus');
        };

        $('#zk-general-div__' + originalId + ' .zk-main-div .zk-p').click(function (e) {
            if ($(this).hasClass('zk-act-p')) {
                $(this).removeClass('zk-act-p').addClass('zk-noact-p');
                if ($(this).hasClass('zk-right-p')) {
                    var sel = leftUl.find('.zk-li-act').addClass('zk-li-act-nofocus');
                    rightUl.append(sel);
                    if (sel.length > 0) {
                        leftAdd.addClass('zk-act-p').removeClass('zk-noact-p');
                    }
                } else if ($(this).hasClass('zk-left-p')) {
                    var sel = rightUl.find('.zk-li-act').addClass('zk-li-act-nofocus');
                    leftUl.append(sel);
                    if (sel.length > 0) {
                        rightAdd.addClass('zk-act-p').removeClass('zk-noact-p');
                    }
                }
                updateOriginalSelect();
            }
        });

        $('#zk-general-div__' + originalId + ' .zk-main-div .zk-range-p').click(function (e) {
            if ($(this).hasClass('zk-act-p')) {
                var selectedDx = $('#zk-general-div__' + originalId + ' .zk-main-div .zk-child-div-right ul .zk-li-act');
                if ($(this).hasClass('zk-top-p')) {
                    var prev = selectedDx.first().prev();
                    selectedDx.each(function () {
                        $(this).insertBefore(prev);
                    });
                } else if ($(this).hasClass('zk-bottom-p')) {
                    var next = selectedDx.last().next();
                    selectedDx.each(function () {
                        $(this).insertAfter(next);
                    });
                }
                updateOriginalSelectAfterRange();
            }
        });

        var updateOriginalSelect = function () {
            rightUl.children().each(function (i, li) {
                $("#" + originalId + " option[value='" + $(li).data('idx') + "']").attr('selected', true);
            });
            leftUl.children().each(function (i, li) {
                $("#" + originalId + " option[value='" + $(li).data('idx') + "']").attr('selected', false);
            });
        };

        var updateOriginalSelectAfterRange = function () {
            rightUl.children().each(function (i, li) {
                $("#" + originalId + " option[value='" + $(li).data('idx') + "']").remove();
                var _option = $('<option value="' + $(li).data('idx') + '">' + $(li).text() + '</option>')
                    .attr('selected', true);
                originalSelect.append(_option);
            });
        };

        var triggerDescr = function (id) {
            if (settings.ZkDescrUrl) {
                $.get(ZkDescrUrl, function (data) {
                        $('#zk-general-div__' + originalId + ' .zk-descr-div').html(data);
                    });
            }
        };
    };


    $.fn.ZkMultiSelectWidget.defaults = {
        ZkHeight: 200,
        ZkWidth: 200,
        ZkSearch: false,
        ZkRange: false,
        ZkDescrUrl: false,
        ZkOptionsDisabled: []
    };

})(jQuery);
