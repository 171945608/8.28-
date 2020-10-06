define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cont/video/index' + location.search,
                    add_url: 'cont/video/add',
                    edit_url: 'cont/video/edit',
                    del_url: 'cont/video/del',
                    multi_url: 'cont/video/multi',
                    table: 'video',
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
                        {field: 'file', title: __('File')},
                        {field: 'title', title: __('Title')},
                        {field: 'content', title: __('Content')},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'location', title: __('Location')},
                        {field: 'longitude', title: __('Longitude')},
                        {field: 'latitude', title: __('Latitude')},
                        // {field: 'is_recommended', title: __('Is_recommended'), searchList: {"1":__('Is_recommended 1'),"0":__('Is_recommended 0')}, table: table, formatter: Table.api.formatter.toggle},
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