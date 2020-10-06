define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'param/app_launch/index' + location.search,
                    add_url: 'param/app_launch/add',
                    edit_url: 'param/app_launch/edit',
                    del_url: 'param/app_launch/del',
                    multi_url: 'param/app_launch/multi',
                    table: 'app_launch',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'field',
                sortName: 'field',
                columns: [
                    [
                        {checkbox: true},
                        // {field: 'field', title: __('Field')},
                        {field: 'title', title: __('Title')},
                        {field: 'type', title: __('Type'), searchList: {"image":__('Type image'),"video":__('Type video')}, formatter: Table.api.formatter.normal},
                        {field: 'file', title: __('File')},
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