define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'vip/goods/index' + location.search,
                    add_url: 'vip/goods/add',
                    edit_url: 'vip/goods/edit',
                    del_url: 'vip/goods/del',
                    multi_url: 'vip/goods/multi',
                    table: 'vip_goods',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'goods_id',
                sortName: 'goods_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'goods_id', title: __('Goods_id')},
                        {field: 'goods_name', title: __('商品名称')},
                        // {field: 'store_id', title: __('Store_id')},
                        // {field: 'vip_cate_id', title: __('Vip_cate_id')},
                        {field: 'vip_cate_name', title: __('VIP分类')},
                        {field: 'vip_price', title: __('Vip_price'), operate: 'BETWEEN'},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'is_recommended',
                            title: __('Is_recommended'),
                            searchList: {"1": __('Yes'), "0": __('No')},
                            table: table,
                            formatter: Table.api.formatter.toggle
                        },
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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