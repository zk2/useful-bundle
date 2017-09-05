$(function () {
    /////////////////////////////////////
    //   Dependent Entity
    /////////////////////////////////////
    if (window.dependentEntityParameters !== undefined) {
        $.each(dependentEntityParameters, function (key, obj) {
            var el = $(obj.total_element);
            var parent_el = $('#' + key);
            parent_el.change(function () {
                obj.data.parent_id = $(this).val();
                $.ajax({
                    type: "POST",
                    data: obj.data,
                    url: obj.url,
                    beforeSend: function () {
                        el.addClass('zk2_select_loader');
                    },
                    success: function (res) {
                        if (res != '') {
                            el.html(res).show();
                            $.each(el.find('option'), function (index, option) {
                                if ($(option).val() == obj.selected_index)
                                    $(option).prop('selected', true);
                            });
                        } else {
                            el.html('<em>' + obj.no_result_msg + '</em>');
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        $('html').html(xhr.responseText);
                    },
                    complete: function () {
                        el.removeClass('zk2_select_loader');
                        el.trigger('change');
                    }
                });
            });
            parent_el.trigger('change');
        });
    }
    /////////////////////////////////////
    //   Entity Ajax Autocomplete
    /////////////////////////////////////
    if (window.entityAjaxAutocompleteParameters !== undefined) {
        $.each(entityAjaxAutocompleteParameters, function (key, obj) {
            var $input = $('#' + key);
            $input.attr('autocomplete', 'off');
            var ul_width = $input.outerWidth(true);
            var data = obj.options;
            data['menu'] = '<ul class="typeahead dropdown-menu" style="width:' + ul_width + 'px;" role="listbox"></ul>';
            data['source'] = function (query, process) {
                $input.addClass('zk2_select_loader');
                return $.post(obj.url, {
                    'prop': query,
                    'class': obj.class,
                    'property': obj.property,
                    'condition_operator': obj.condition_operator,
                    'query': obj.query,
                    'em_name': obj.em_name,
                    'max_rows': obj.max_rows
                }, function (data) {
                    $input.removeClass('zk2_select_loader');
                    return process(data.options);
                });
            };
            $input.typeahead(data);
        });
    }
    /////////////////////////////////////
    //   Select2 Multiple Entity
    /////////////////////////////////////
    if (window.select2MultipleEntityParameters !== undefined) {
        $.each(select2MultipleEntityParameters, function (key, obj) {
            var $input = $('#' + key);
            var data = {
                multiple: true,
                minimumInputLength: 1,
                templateResult: functionFormat,
                templateSelection: functionFormatSelection,
                ajax: {}
            };
            $.each(obj.options, function (k, v) {
                data[k] = v;
            });
            data['ajax'] = {
                url: obj.url,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        'prop': params.term,
                        'class': obj.class,
                        'property': obj.property,
                        'condition_operator': obj.condition_operator,
                        'em_name': obj.em_name,
                        'max_rows': obj.max_rows
                    };
                },
                processResults: function (data, page) {
                    return {results: data.options};
                },
                cache: true
            };
            $input.select2(data);
        });
        function functionFormat(item) {
            if (item.loading) return 'Searching...';
            return '<div class="clearfix"><div class="col-sm-12">' + item.name || item.text + '</div></div>';
        }
        function functionFormatSelection(item) {
            if (item.name) item.text = item.name;
            item.selected = true;
            return item.name || item.text;
        }
    }
    /////////////////////////////////////
    //   ZkMultiSelect Widget
    /////////////////////////////////////
    if (window.zkMultipleTypeParameters !== undefined) {
        $.each(zkMultipleTypeParameters, function (key, obj) {
            var $input = $('#' + key);
            $input.ZkMultiSelectWidget(obj);
        });
    }
});



