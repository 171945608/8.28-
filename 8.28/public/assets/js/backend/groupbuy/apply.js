define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'groupbuy/apply/index' + location.search,
                    add_url: 'groupbuy/apply/add',
                    edit_url: 'groupbuy/apply/edit',
                    del_url: 'groupbuy/apply/del',
                    multi_url: 'groupbuy/apply/multi',
                    table: 'groupbuy_apply',
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
                        {field: 'groupbuy_id', title: __('Groupbuy_id')},
                        {field: 'groupbuy_title', title: __('团购标题')},
                        {field: 'store_id', title: __('Store_id')},
                        {field: 'store_name', title: __('店铺名称')},
                        {field: 'goods_data', title: __('报名商品')},
                        {field: 'user_id', title: __('下单用户ID')},
                        {field: 'user_name', title: __('下单用户昵称')},
                        {field: 'realname', title: __('Realname')},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'is_read', title: __('Is_read'), searchList: {"0":__('Is_read 0'),"1":__('Is_read 1')}, formatter: Table.api.formatter.normal},
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