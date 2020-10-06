define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: "groupbuy/goods/index/ids/" + Config.ids + location.search,
                    add_url: 'groupbuy/goods/add',
                    edit_url: 'groupbuy/goods/edit',
                    del_url: 'groupbuy/goods/del',
                    multi_url: 'groupbuy/goods/multi',
                    table: 'groupbuy_goods',
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
                        {field: 'id', title: __('团购商品ID')},
                        {field: 'name', title: __('商品名称')},
                        {field: 'image', title: __('商品主图'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'store_name', title: __('店铺名称')},
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