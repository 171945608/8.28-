define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'param/base/index' + location.search,
                    add_url: 'param/base/add',
                    edit_url: 'param/base/edit',
                    del_url: 'param/base/del',
                    multi_url: 'param/base/multi',
                    table: 'param',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        // {field: 'name', title: __('Name')},
                        {field: 'title', title: __('Title')},
                        {field: 'value', title: __('Value')},
                        {field: 'type', title: __('Type'), searchList: {"string":__('String'),"array":__('Array')}, formatter: Table.api.formatter.normal},
                        // {field: 'group', title: __('Group')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});