define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'search/keywords/index' + location.search,
                    add_url: 'search/keywords/add',
                    edit_url: 'search/keywords/edit',
                    del_url: 'search/keywords/del',
                    multi_url: 'search/keywords/multi',
                    table: 'search_keywords',
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
                        {field: 'word', title: __('Word')},
                        {
                            field: 'group', title: __('Group'), searchList: {
                                "goods": __('Group goods'),
                                "store": __('Group store'),
                                "groupbuy": __('Group groupbuy'),
                                "purchase": __('Group purchase'),
                                "discount": __('Group discount'),
                                "union": __('Group union'),
                                "express": __('Group express'),
                                "vip": __('Group VIP'),
                                "topic": __('Group topic'),
                            }, formatter: Table.api.formatter.normal
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
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